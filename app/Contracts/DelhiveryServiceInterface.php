<?php

namespace App\Contracts;

interface DelhiveryServiceInterface
{
    public function checkPincode(string $pincode): array;

    public function calculateShippingCost(array $payload): array;

    public function generateShippingLabel(string $waybill, bool $pdf = false): array;

    public function trackShipment(string $waybill, ?string $refIds = null): array;

    /**
     * Generate one or more waybills.
     */
    public function generateWaybills(int $count = 1): array;

    /**
     * Create a shipment in Delhivery (CMU create).
     */
    public function createShipment(array $payload): array;

    /**
     * Update shipment-level attributes (pt, cod, gm, etc).
     */
    public function updateShipment(array $payload): array;

    /**
     * Cancel a shipment in Delhivery.
     */
    public function cancelShipment(string $waybill): array;

    /**
     * Create a pickup request.
     */
    public function createPickup(array $payload): array;
}
