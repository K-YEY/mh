<?php

namespace App\Services\Operations;

use App\Models\MasterShipment;

class CostAllocationService
{
    /**
     * Calculate financial summary and P&L margin for a Master Shipment
     */
    public function calculateShipmentProfitability(MasterShipment $shipment): array
    {
        $shipment->load(['invoices.items', 'houseShipments.invoices.items']);

        $customerInvoicedUsd = 0.00;
        $vendorBillsUsd = 0.00;

        foreach ($shipment->invoices as $inv) {
            $amountInBase = $inv->total_amount * $inv->exchange_rate;
            if ($inv->invoice_type === 'CUSTOMER_INVOICE') {
                $customerInvoicedUsd += $amountInBase;
            } else {
                $vendorBillsUsd += $amountInBase;
            }
        }

        foreach ($shipment->houseShipments as $house) {
            foreach ($house->invoices as $inv) {
                $amountInBase = $inv->total_amount * $inv->exchange_rate;
                if ($inv->invoice_type === 'CUSTOMER_INVOICE') {
                    $customerInvoicedUsd += $amountInBase;
                } else {
                    $vendorBillsUsd += $amountInBase;
                }
            }
        }

        $grossProfitUsd = $customerInvoicedUsd - $vendorBillsUsd;
        $profitMarginPercent = $customerInvoicedUsd > 0
            ? round(($grossProfitUsd / $customerInvoicedUsd) * 100, 2)
            : 0.00;

        return [
            'total_revenue_usd' => round($customerInvoicedUsd, 2),
            'total_cost_usd' => round($vendorBillsUsd, 2),
            'net_profit_usd' => round($grossProfitUsd, 2),
            'margin_percent' => $profitMarginPercent,
        ];
    }
}
