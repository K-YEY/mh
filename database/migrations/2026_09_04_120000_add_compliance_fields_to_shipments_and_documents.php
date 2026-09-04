<?php

use App\Models\DocumentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComplianceFieldsToShipmentsAndDocuments extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add customs_broker_id, trucker_id, coo_type, compliance_status to master_shipments
        Schema::table('master_shipments', function (Blueprint $table) {
            if (! Schema::hasColumn('master_shipments', 'customs_broker_id')) {
                $table->foreignId('customs_broker_id')->nullable()->after('shipping_line_id')->constrained('parties')->nullOnDelete();
            }
            if (! Schema::hasColumn('master_shipments', 'trucker_id')) {
                $table->foreignId('trucker_id')->nullable()->after('customs_broker_id')->constrained('parties')->nullOnDelete();
            }
            if (! Schema::hasColumn('master_shipments', 'coo_type')) {
                $table->string('coo_type', 50)->nullable()->default('GENERAL')->after('trucker_id');
            }
            if (! Schema::hasColumn('master_shipments', 'compliance_status')) {
                $table->string('compliance_status', 50)->default('PENDING_DOCS')->after('status');
            }
        });

        // 2. Add metadata JSON column to shipment_documents
        Schema::table('shipment_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('shipment_documents', 'metadata')) {
                $table->json('metadata')->nullable()->after('rejection_notes');
            }
        });

        // 3. Seed / Ensure standard Document Types for Agro / Reefer & Customs Compliance
        $docTypes = [
            [
                'code' => 'COO',
                'name_ar' => 'شهادة المنشأ',
                'name_en' => 'Certificate of Origin',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'PHYTO',
                'name_ar' => 'شهادة صحة نباتية',
                'name_en' => 'Phytosanitary Certificate',
                'requires_expiry_date' => true,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'FUMIGATION',
                'name_ar' => 'شهادة التبخير والتعقيم',
                'name_en' => 'Fumigation Certificate',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'MRL_TEST',
                'name_ar' => 'تقرير فحص متبقيات المبيدات',
                'name_en' => 'Pesticide Residue / MRL Test Report',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'QUALITY_INSP',
                'name_ar' => 'تقرير فحص ومطابقة الجودة',
                'name_en' => 'Quality Inspection Report',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'DATA_LOGGER',
                'name_ar' => 'تفريغ قراءات مسجلات الحرارة',
                'name_en' => 'Data Logger / Temperature Records',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'COMM_INV',
                'name_ar' => 'الفاتورة التجارية النهائية',
                'name_en' => 'Commercial Invoice',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'PACKING_LIST',
                'name_ar' => 'بيان العبوة ومطابقة ISPM 15',
                'name_en' => 'Packing List',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'BOL',
                'name_ar' => 'بوليصة الشحن البحرية',
                'name_en' => 'Bill of Lading (B/L)',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'DELIVERY_ORDER',
                'name_ar' => 'إذن التسليم الملاحي',
                'name_en' => 'Delivery Order (D/O)',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'CONTAINER_MANIFEST',
                'name_ar' => 'بيان الحاويات ورصاص التأمين',
                'name_en' => 'Container & Seal Manifest',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'CUSTOMS_CLEARANCE',
                'name_ar' => 'القيد المسبق الجمركي (ACID / SABER)',
                'name_en' => 'Advance Cargo Filing / Pre-registration',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
            [
                'code' => 'CUSTOMS_AUTH',
                'name_ar' => 'تفويض التخليص الجمركي وإقرار القيمة',
                'name_en' => 'Customs Broker Power of Attorney & Valuation',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ],
        ];

        foreach ($docTypes as $docType) {
            DocumentType::firstOrCreate(['code' => $docType['code']], $docType);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_documents', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_documents', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });

        Schema::table('master_shipments', function (Blueprint $table) {
            if (Schema::hasColumn('master_shipments', 'customs_broker_id')) {
                $table->dropConstrainedForeignId('customs_broker_id');
            }
            if (Schema::hasColumn('master_shipments', 'trucker_id')) {
                $table->dropConstrainedForeignId('trucker_id');
            }
            if (Schema::hasColumn('master_shipments', 'coo_type')) {
                $table->dropColumn('coo_type');
            }
            if (Schema::hasColumn('master_shipments', 'compliance_status')) {
                $table->dropColumn('compliance_status');
            }
        });
    }
}

return new AddComplianceFieldsToShipmentsAndDocuments;
