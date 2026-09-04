<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentInvoice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'party_id',
        'master_shipment_id',
        'house_shipment_id',
        'currency_code',
        'exchange_rate',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_total',
        'total_amount',
        'status',
        'journal_entry_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function masterShipment(): BelongsTo
    {
        return $this->belongsTo(MasterShipment::class, 'master_shipment_id');
    }

    public function houseShipment(): BelongsTo
    {
        return $this->belongsTo(HouseShipment::class, 'house_shipment_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentInvoiceItem::class, 'shipment_invoice_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
