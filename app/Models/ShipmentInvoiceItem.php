<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentInvoiceItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shipment_invoice_id',
        'charge_item_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ShipmentInvoice::class, 'shipment_invoice_id');
    }

    public function chargeItem(): BelongsTo
    {
        return $this->belongsTo(ChargeItem::class, 'charge_item_id');
    }
}
