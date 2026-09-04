<?php

namespace App\Actions\Operations;

use App\Models\MasterShipment;
use App\Services\Audit\AuditLoggerService;
use App\Services\Sequencing\SequenceGeneratorService;

class CreateMasterShipmentAction
{
    public function __construct(
        protected SequenceGeneratorService $sequenceGenerator
    ) {}

    public function execute(array $data): MasterShipment
    {
        $year = (int) date('Y');
        $docType = ($data['direction'] ?? 'EXPORT') === 'EXPORT' ? 'MASTER_EXPORT' : 'MASTER_IMPORT';
        $fileNumber = $this->sequenceGenerator->getNextSequence($docType, $year);

        $shipment = MasterShipment::create(array_merge($data, [
            'file_number' => $fileNumber,
            'status' => 'BOOKING_CONFIRMED',
            'created_by_user_id' => auth()->id() ?? 1,
        ]));

        AuditLoggerService::log($shipment, 'create', "Created Ocean Master Shipment {$shipment->file_number}");

        return $shipment;
    }
}
