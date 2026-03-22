<?php

namespace App\Contracts;

interface DelhiveryServiceInterface
{
    public function checkPincode(string $pincode): array;

    public function calculateShippingCost(array $payload): array;

    public function generateShippingLabel(string $waybill, bool $pdf = false): array;

    public function trackShipment(string $waybill, ?string $refIds = null): array;
}