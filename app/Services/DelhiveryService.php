<?php

namespace App\Services;

use App\Contracts\DelhiveryServiceInterface;
use App\Exceptions\DelhiveryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DelhiveryService implements DelhiveryServiceInterface
{
    private string $baseUrl;
    private string $token;
    private int $timeout;
    private int $retryTimes;
    private int $retrySleep;

    public function __construct()
    {
        $this->baseUrl    = rtrim((string) config('services.delhivery.base_url'), '/');
        $this->token      = (string) config('services.delhivery.token');
        $this->timeout    = (int) config('services.delhivery.timeout', 30);
        $this->retryTimes = (int) config('services.delhivery.retry_times', 3);
        $this->retrySleep = (int) config('services.delhivery.retry_sleep', 500);

        if (empty($this->token)) {
            throw new DelhiveryException('Delhivery token is not configured in env.');
        }
    }

    public function checkPincode(string $pincode): array
    {
        $endpoint = '/c/api/pin-codes/json/';
        $query = [
            'filter_codes' => $pincode,
        ];

        $response = $this->sendGetRequest($endpoint, $query, 'check_pincode');

        return $this->formatSuccessResponse($response, 'Pincode details fetched successfully.');
    }

    public function calculateShippingCost(array $payload): array
    {
        $endpoint = '/api/kinko/v1/invoice/charges/.json';

        $query = [
            'md'    => $payload['md'] ?? 'S',
            'ss'    => $payload['ss'] ?? 'RTO',
            'd_pin' => $payload['d_pin'],
            'o_pin' => $payload['o_pin'],
            'cgm'   => $payload['cgm'],
            'pt'    => $payload['pt'] ?? 'Pre-paid',
            'cod'   => $payload['cod'] ?? 0,
        ];

        $response = $this->sendGetRequest($endpoint, $query, 'calculate_shipping_cost');

        return $this->formatSuccessResponse($response, 'Shipping cost fetched successfully.');
    }

    public function generateShippingLabel(string $waybill, bool $pdf = false): array
    {
        $endpoint = '/api/p/packing_slip';

        $query = [
            'wbns' => $waybill,
            'pdf'  => $pdf ? 'true' : 'false',
        ];

        $response = $this->sendGetRequest($endpoint, $query, 'generate_shipping_label');

        return $this->formatSuccessResponse($response, 'Shipping label response fetched successfully.');
    }

    public function trackShipment(string $waybill, ?string $refIds = null): array
    {
        $endpoint = '/api/v1/packages/json/';

        $query = [
            'waybill' => $waybill,
            'ref_ids' => $refIds ?? '',
        ];

        $response = $this->sendGetRequest($endpoint, $query, 'track_shipment');

        return $this->formatSuccessResponse($response, 'Shipment tracking details fetched successfully.');
    }

    public function generateWaybills(int $count = 1): array
    {
        $endpoint = '/waybill/api/bulk/json/';

        $query = [
            // Delhivery examples pass token in query; we already send it as header as well.
            'token' => $this->token,
            'count' => $count,
        ];

        $response = $this->sendGetRequest($endpoint, $query, 'generate_waybills');

        return $this->formatSuccessResponse($response, 'Waybills generated successfully.');
    }

    public function createShipment(array $payload): array
    {
        $endpoint = '/api/cmu/create.json';

        // Delhivery examples expect form fields: format=json & data=<JSON string>
        $body = [
            'format' => 'json',
            'data'   => json_encode($payload),
        ];

        $response = $this->sendPostRequest($endpoint, $body, 'create_shipment', true);

        return $this->formatSuccessResponse($response, 'Shipment created successfully.');
    }

    public function updateShipment(array $payload): array
    {
        $endpoint = '/api/p/edit';

        $response = $this->sendPostRequest($endpoint, $payload, 'update_shipment');

        return $this->formatSuccessResponse($response, 'Shipment updated successfully.');
    }

    public function cancelShipment(string $waybill): array
    {
        $endpoint = '/api/p/edit';

        $body = [
            'waybill'     => $waybill,
            'cancellation'=> 'true',
        ];

        $response = $this->sendPostRequest($endpoint, $body, 'cancel_shipment');

        return $this->formatSuccessResponse($response, 'Shipment cancelled successfully.');
    }

    public function createPickup(array $payload): array
    {
        $endpoint = '/fm/request/new/';

        $response = $this->sendPostRequest($endpoint, $payload, 'create_pickup');

        return $this->formatSuccessResponse($response, 'Pickup request created successfully.');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => 'Token ' . $this->token,
                'Accept'        => 'application/json',
            ])
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, function ($exception) {
                return $exception instanceof ConnectionException;
            });
    }

    private function sendPostRequest(string $endpoint, array $body = [], string $action = 'delhivery_post', bool $asForm = false): Response
    {
        try {
            Log::info('Delhivery API POST initiated', [
                'action'   => $action,
                'endpoint' => $endpoint,
                'body'     => $body,
                'base_url' => $this->baseUrl,
            ]);

            $client = $this->client();
            if ($asForm) {
                $client = $client->asForm();
            }

            $response = $client->post($endpoint, $body);

            Log::info('Delhivery raw POST response', [
                'action'  => $action,
                'status'  => $response->status(),
                'body'    => $response->body(),
                'json'    => $response->json(),
            ]);

            if ($response->failed()) {
                $this->throwApiException($response, $action, $endpoint, $body);
            }

            return $response;
        } catch (ConnectionException $e) {
            Log::error('Delhivery POST connection exception', [
                'action'  => $action,
                'message' => $e->getMessage(),
            ]);

            throw new DelhiveryException(
                'Unable to connect to Delhivery API: ' . $e->getMessage(),
                503,
                ['action' => $action, 'endpoint' => $endpoint]
            );
        } catch (\Throwable $e) {
            Log::error('Delhivery POST unexpected exception', [
                'action'  => $action,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw new DelhiveryException(
                'Request to Delhivery API failed: ' . $e->getMessage(),
                500,
                ['action' => $action, 'endpoint' => $endpoint]
            );
        }
    }

    private function sendGetRequest(string $endpoint, array $query = [], string $action = 'delhivery_get'): Response
    {
        try {
            Log::info('Delhivery API request initiated', [
                'action'   => $action,
                'endpoint' => $endpoint,
                'query'    => $query,
                'base_url' => $this->baseUrl,
            ]);

            $response = $this->client()->get($endpoint, $query);

            Log::info('Delhivery raw response', [
                'action'  => $action,
                'status'  => $response->status(),
                'body'    => $response->body(),
                'json'    => $response->json(),
            ]);

            if ($response->failed()) {
                $this->throwApiException($response, $action, $endpoint, $query);
            }

            return $response;
        } catch (ConnectionException $e) {
            Log::error('Delhivery connection exception', [
                'action'  => $action,
                'message' => $e->getMessage(),
            ]);

            throw new DelhiveryException(
                'Unable to connect to Delhivery API: ' . $e->getMessage(),
                503,
                ['action' => $action, 'endpoint' => $endpoint]
            );
        } catch (\Throwable $e) {
            Log::error('Delhivery unexpected exception', [
                'action'  => $action,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw new DelhiveryException(
                'Request to Delhivery API failed: ' . $e->getMessage(),
                500,
                ['action' => $action, 'endpoint' => $endpoint]
            );
        }
    }

    private function throwApiException(Response $response, string $action, string $endpoint, array $query): void
    {
        $json = $response->json();

        $message = is_array($json)
            ? (
                $json['rmk']
                ?? $json['Error']
                ?? $json['error']
                ?? $json['message']
                ?? 'Delhivery API request failed.'
            )
            : 'Delhivery API request failed.';

        Log::warning('Delhivery API returned failure response', [
            'action'   => $action,
            'endpoint' => $endpoint,
            'query'    => $query,
            'status'   => $response->status(),
            'body'     => $json,
        ]);

        throw new DelhiveryException($message, $response->status(), [
            'action'   => $action,
            'endpoint' => $endpoint,
            'query'    => $query,
            'response' => $json,
        ]);
    }

    private function formatSuccessResponse(Response $response, string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
            'status'  => $response->status(),
            'data'    => $response->json(),
        ];
    }
}
