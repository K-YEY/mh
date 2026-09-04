<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShipmentDocument extends Model
{
    protected $fillable = [
        'document_type_id',
        'documentable_type',
        'documentable_id',
        'reference_number',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'file_path',
        'file_name',
        'file_size_bytes',
        'mime_type',
        'status',
        'rejection_notes',
        'metadata',
        'verified_by_user_id',
        'verified_at',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'file_size_bytes' => 'integer',
        'metadata' => 'array',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
