<?php

namespace App\Http\Controllers\Api;

use App\Contracts\DelhiveryServiceInterface;
use App\Exceptions\DelhiveryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delhivery\CalculateShippingCostRequest;
use App\Http\Requests\Delhivery\GenerateShippingLabelRequest;
use App\Http\Requests\Delhivery\TrackShipmentRequest;
use App\Http\Resources\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DelhiveryController extends Controller
{
    public function __construct(
        private readonly DelhiveryServiceInterface $delhiveryService
    ) {
    }

    public function checkPincode(string $pincode)
    {
        try {
            if (!preg_match('/^\d{6}$/', $pincode)) {
                return ApiResponse::error('Pincode must be exactly 6 digits.', 422);
            }

            $result = $this->delhiveryService->checkPincode($pincode);

            return ApiResponse::success(
                $result['data'],
                $result['message'],
                $result['status']
            );
        } catch (DelhiveryException $e) {
            return $this->handleDelhiveryException($e);
        } catch (\Throwable $e) {
            return $this->handleUnknownException($e, 'check_pincode');
        }
    }

    public function calculateShippingCost(CalculateShippingCostRequest $request)
    {
        try {
            $result = $this->delhiveryService->calculateShippingCost($request->validated());

            return ApiResponse::success(
                $result['data'],
                $result['message'],
                $result['status']
            );
        } catch (DelhiveryException $e) {
            return $this->handleDelhiveryException($e);
        } catch (\Throwable $e) {
            return $this->handleUnknownException($e, 'calculate_shipping_cost');
        }
    }

    public function generateShippingLabel(GenerateShippingLabelRequest $request)
    {
        try {
            $result = $this->delhiveryService->generateShippingLabel(
                $request->string('waybill')->toString(),
                $request->boolean('pdf', false)
            );

            return ApiResponse::success(
                $result['data'],
                $result['message'],
                $result['status']
            );
        } catch (DelhiveryException $e) {
            return $this->handleDelhiveryException($e);
        } catch (\Throwable $e) {
            return $this->handleUnknownException($e, 'generate_shipping_label');
        }
    }

    public function trackShipment(TrackShipmentRequest $request)
    {
        try {
            $result = $this->delhiveryService->trackShipment(
                (string) $request->input('waybill', ''),
                $request->input('ref_ids')
            );

            return ApiResponse::success(
                $result['data'],
                $result['message'],
                $result['status']
            );
        } catch (DelhiveryException $e) {
            return $this->handleDelhiveryException($e);
        } catch (\Throwable $e) {
            return $this->handleUnknownException($e, 'track_shipment');
        }
    }

    private function handleDelhiveryException(DelhiveryException $e)
    {
        return ApiResponse::error(
            $e->getMessage(),
            $e->getCode() > 0 ? $e->getCode() : 500,
            null,
            [],
            $e->getContext()
        );
    }

    private function handleUnknownException(\Throwable $e, string $action)
    {
        Log::error('Unexpected Delhivery controller error', [
            'action' => $action,
            'error'  => $e->getMessage(),
            'trace'  => $e->getTraceAsString(),
        ]);

        return ApiResponse::error('Unexpected server error.', 500);
    }
}