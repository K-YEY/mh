<?php

namespace App\Services\Sequencing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SequenceGeneratorService
{
    /**
     * Map document types to default prefixes
     */
    protected const PREFIX_MAP = [
        'MASTER_EXPORT' => 'EXP',
        'MASTER_IMPORT' => 'IMP',
        'HOUSE_CONSIGNMENT' => 'HOCN',
        'QUOTATION' => 'QT',
        'CUSTOMER_INVOICE' => 'INV',
        'VENDOR_BILL' => 'BILL',
        'JOURNAL_ENTRY' => 'JV',
        'PAYMENT_VOUCHER' => 'PV',
        'RECEIPT_VOUCHER' => 'RV',
    ];

    /**
     * Map document types to table and column name for collision detection
     */
    protected const TABLE_MAP = [
        'MASTER_EXPORT' => ['master_shipments', 'file_number'],
        'MASTER_IMPORT' => ['master_shipments', 'file_number'],
        'HOUSE_CONSIGNMENT' => ['house_shipments', 'house_file_number'],
        'CUSTOMER_INVOICE' => ['shipment_invoices', 'invoice_number'],
        'VENDOR_BILL' => ['shipment_invoices', 'invoice_number'],
        'JOURNAL_ENTRY' => ['journal_entries', 'entry_number'],
        'PAYMENT_VOUCHER' => ['payment_vouchers', 'voucher_number'],
        'RECEIPT_VOUCHER' => ['payment_vouchers', 'voucher_number'],
    ];

    /**
     * Generate next sequence number with pessimistic row-locking to prevent race conditions.
     */
    public function getNextSequence(string $documentType, ?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $prefix = self::PREFIX_MAP[$documentType] ?? strtoupper($documentType);

        return DB::transaction(function () use ($documentType, $prefix, $year) {
            // Lock row for update
            $seq = DB::table('document_sequences')
                ->where('prefix', $prefix)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $seq) {
                $startNum = 1;

                if (isset(self::TABLE_MAP[$documentType])) {
                    [$table, $column] = self::TABLE_MAP[$documentType];
                    if (Schema::hasTable($table)) {
                        $maxCount = DB::table($table)->count();
                        if ($maxCount > 0) {
                            $startNum = $maxCount + 1;
                        }
                    }
                }

                DB::table('document_sequences')->insert([
                    'prefix' => $prefix,
                    'year' => $year,
                    'last_number' => $startNum,
                    'format' => '{PREFIX}-{YEAR}-{NUMBER:5}',
                    'updated_at' => now(),
                ]);
                $nextNum = $startNum;
            } else {
                $nextNum = $seq->last_number + 1;
                DB::table('document_sequences')
                    ->where('id', $seq->id)
                    ->update([
                        'last_number' => $nextNum,
                        'updated_at' => now(),
                    ]);
            }

            return sprintf('%s-%d-%05d', $prefix, $year, $nextNum);
        });
    }
}
