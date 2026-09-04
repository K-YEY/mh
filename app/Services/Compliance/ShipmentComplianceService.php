<?php

namespace App\Services\Compliance;

use App\Models\HouseShipment;
use App\Models\MasterShipment;

class ShipmentComplianceService
{
    /**
     * Get the list of compliance document specifications for Agro / Reefer cargo.
     */
    public static function getDocumentSpecs(): array
    {
        return [
            // 1. Agro & Technical Docs
            'PHYTO' => [
                'code' => 'PHYTO',
                'name_ar' => 'شهادة الصحة النباتية',
                'name_en' => 'Phytosanitary Certificate',
                'category' => 'agro',
                'is_mandatory' => true,
                'description_ar' => 'تشمل رقم الشهادة، تاريخ الصلاحية، كود محطة التعبئة والمزرعة المعتمدة',
                'description_en' => 'Includes cert #, expiry date, approved packhouse / farm code',
            ],
            'FUMIGATION' => [
                'code' => 'FUMIGATION',
                'name_ar' => 'شهادة التبخير والتعقيم',
                'name_en' => 'Fumigation Certificate',
                'category' => 'agro',
                'is_mandatory' => false,
                'description_ar' => 'نوع الغاز (Methyl Bromide/Phosphine) والجرعة وتاريخ المعاملة',
                'description_en' => 'Gas type, dosage, and treatment date',
            ],
            'MRL_TEST' => [
                'code' => 'MRL_TEST',
                'name_ar' => 'تقرير فحص متبقيات المبيدات',
                'name_en' => 'Pesticide Residue / MRL Test Report',
                'category' => 'agro',
                'is_mandatory' => true,
                'description_ar' => 'معمل معتمد ونتيجة الفحص ومطابقة نسب الـ MRL للمقصد',
                'description_en' => 'Accredited lab, test results, destination MRL compliance',
            ],
            'QUALITY_INSP' => [
                'code' => 'QUALITY_INSP',
                'name_ar' => 'تقرير فحص ومطابقة الجودة',
                'name_en' => 'Quality Inspection Report',
                'category' => 'agro',
                'is_mandatory' => false,
                'description_ar' => 'تقرير مطابقة الرتبة والصلابة والبركس والتعبئة',
                'description_en' => 'Class grading, brix, firmness and packaging audit',
            ],
            'DATA_LOGGER' => [
                'code' => 'DATA_LOGGER',
                'name_ar' => 'تفريغ مسجلات الحرارة وسلسلة التبريد',
                'name_en' => 'Data Logger / Cold Chain Records',
                'category' => 'agro',
                'is_mandatory' => false,
                'description_ar' => 'أرقام حساسات التبريد وتفريغ درجات الحرارة لضمان عدم انكسار التبريد',
                'description_en' => 'Reefer data logger readings to ensure unbroken cold chain',
            ],

            // 2. Commercial & Origin Docs
            'COMM_INV' => [
                'code' => 'COMM_INV',
                'name_ar' => 'الفاتورة التجارية النهائية',
                'name_en' => 'Commercial Invoice',
                'category' => 'commercial',
                'is_mandatory' => true,
                'description_ar' => 'شروط الشحن (Incoterms) والعملة والقيمة الإجمالية المصرح عنها',
                'description_en' => 'Incoterms (FOB/CIF), currency, and total invoice value',
            ],
            'PACKING_LIST' => [
                'code' => 'PACKING_LIST',
                'name_ar' => 'بيان العبوة ومطابقة ISPM 15',
                'name_en' => 'Packing List',
                'category' => 'commercial',
                'is_mandatory' => true,
                'description_ar' => 'الأوزان الإجمالية والصافية وعدد الطرود ونوع الطبالي المعالجة',
                'description_en' => 'Gross/Net weight, package counts, ISPM 15 heat treated pallets',
            ],
            'COO' => [
                'code' => 'COO',
                'name_ar' => 'شهادة المنشأ الرسمية',
                'name_en' => 'Certificate of Origin (EUR.1 / GAFTA / Agadir)',
                'category' => 'commercial',
                'is_mandatory' => true,
                'description_ar' => 'تحديد نوع الشهادة (EUR.1، أغادير، GAFTA، عامة) ورقم اعتماد الغرفة التجارية',
                'description_en' => 'Origin cert type (EUR.1, GAFTA, Agadir), chamber of commerce endorsement',
            ],

            // 3. Shipping & Carrier Docs
            'BOL' => [
                'code' => 'BOL',
                'name_ar' => 'بوليصة الشحن البحرية (B/L)',
                'name_en' => 'Bill of Lading (Original / Telex)',
                'category' => 'shipping',
                'is_mandatory' => true,
                'description_ar' => 'مطابقة أرقام الحاويات والرصاص ودرجة التبريد ونسبة التهوية',
                'description_en' => 'Container numbers, seal numbers, temp & vent settings match',
            ],
            'CONTAINER_MANIFEST' => [
                'code' => 'CONTAINER_MANIFEST',
                'name_ar' => 'بيان الحاويات ورصاص التأمين',
                'name_en' => 'Container & Seal Manifest',
                'category' => 'shipping',
                'is_mandatory' => false,
                'description_ar' => 'تفاصيل الحاويات، الرصاص الملاحي، وأوزان الفوارغ والحمولة',
                'description_en' => 'Manifest of container numbers, seals, and tare/payloads',
            ],
            'DELIVERY_ORDER' => [
                'code' => 'DELIVERY_ORDER',
                'name_ar' => 'إذن التسليم الملاحي (D/O)',
                'name_en' => 'Delivery Order (D/O)',
                'category' => 'shipping',
                'is_mandatory' => false,
                'description_ar' => 'إذن التسليم المفرج عنه بعد سداد العميل وموافقة الخط',
                'description_en' => 'Release authorized after financial clearance & carrier approval',
            ],

            // 4. Customs & Regulatory
            'CUSTOMS_CLEARANCE' => [
                'code' => 'CUSTOMS_CLEARANCE',
                'name_ar' => 'القيد المسبق الجمركي (ACID / SABER)',
                'name_en' => 'Advance Cargo Filing / ACID / SABER',
                'category' => 'customs',
                'is_mandatory' => false,
                'description_ar' => 'أرقام القيد المسبق (نافذة ACID بمصر، سابر بالسعودية، EIN بأمريكا)',
                'description_en' => 'Pre-registration cargo filing & destination importer tax ID',
            ],
            'CUSTOMS_AUTH' => [
                'code' => 'CUSTOMS_AUTH',
                'name_ar' => 'تفويض التخليص الجمركي وإقرار القيمة',
                'name_en' => 'Customs Broker Power of Attorney',
                'category' => 'customs',
                'is_mandatory' => false,
                'description_ar' => 'توكيل المخلص الجمركي المعتمد وإقرار القيمة للأغراض الجمركية',
                'description_en' => 'Customs broker authorization and customs valuation statement',
            ],
        ];
    }

    /**
     * Check compliance status for a MasterShipment or HouseShipment.
     */
    public function checkCompliance(MasterShipment|HouseShipment $shipment): array
    {
        $specs = self::getDocumentSpecs();

        // Load existing documents with their types
        $documents = $shipment->documents()->with('documentType')->get();

        $checklist = [];
        $mandatoryCount = 0;
        $verifiedMandatoryCount = 0;
        $uploadedCount = 0;
        $missingMandatory = [];
        $warnings = [];

        foreach ($specs as $code => $spec) {
            $matchedDoc = $documents->first(function ($doc) use ($code) {
                if ($doc->documentType?->code === $code) {
                    return true;
                }
                if ($code === 'COO' && $doc->documentType?->code === 'COO_EUR1') {
                    return true;
                }
                if ($code === 'MRL_TEST' && $doc->documentType?->code === 'PEST_LAB') {
                    return true;
                }

                return false;
            });

            $isUploaded = $matchedDoc !== null;
            $isVerified = $matchedDoc && $matchedDoc->status === 'VERIFIED';
            $status = 'MISSING';

            if ($matchedDoc) {
                $status = $matchedDoc->status;
                $uploadedCount++;
            }

            if ($spec['is_mandatory']) {
                $mandatoryCount++;
                if ($isVerified) {
                    $verifiedMandatoryCount++;
                } else {
                    $missingMandatory[] = $spec['name_ar'];
                    if (! $isUploaded) {
                        $warnings[] = "مستند إلزامي ناقص: {$spec['name_ar']} ({$spec['name_en']})";
                    } elseif ($status === 'REJECTED') {
                        $warnings[] = "تم رفض مستند: {$spec['name_ar']} - {$matchedDoc->rejection_notes}";
                    } else {
                        $warnings[] = "مستند قيد المراجعة والاعتماد: {$spec['name_ar']}";
                    }
                }
            }

            $checklist[] = [
                'code' => $spec['code'],
                'name_ar' => $spec['name_ar'],
                'name_en' => $spec['name_en'],
                'category' => $spec['category'],
                'is_mandatory' => $spec['is_mandatory'],
                'description_ar' => $spec['description_ar'],
                'description_en' => $spec['description_en'],
                'is_uploaded' => $isUploaded,
                'is_verified' => $isVerified,
                'status' => $status,
                'document' => $matchedDoc ? [
                    'id' => $matchedDoc->id,
                    'reference_number' => $matchedDoc->reference_number,
                    'issuing_authority' => $matchedDoc->issuing_authority,
                    'issue_date' => $matchedDoc->issue_date?->format('Y-m-d'),
                    'expiry_date' => $matchedDoc->expiry_date?->format('Y-m-d'),
                    'file_name' => $matchedDoc->file_name,
                    'file_path' => $matchedDoc->file_path,
                    'status' => $matchedDoc->status,
                    'rejection_notes' => $matchedDoc->rejection_notes,
                    'metadata' => $matchedDoc->metadata,
                    'created_at' => $matchedDoc->created_at?->toIso8601String(),
                ] : null,
            ];
        }

        $completionPercent = $mandatoryCount > 0
            ? (int) round(($verifiedMandatoryCount / $mandatoryCount) * 100)
            : 100;

        $isReadyForRelease = ($verifiedMandatoryCount === $mandatoryCount);

        $complianceStatus = 'PENDING_DOCS';
        if ($isReadyForRelease) {
            $complianceStatus = 'COMPLIANT';
        } elseif ($uploadedCount > 0) {
            $complianceStatus = 'IN_REVIEW';
        }

        // Auto-sync compliance_status on MasterShipment if changed
        if ($shipment instanceof MasterShipment && $shipment->compliance_status !== $complianceStatus) {
            $shipment->update(['compliance_status' => $complianceStatus]);
        }

        return [
            'checklist' => $checklist,
            'total_mandatory' => $mandatoryCount,
            'verified_mandatory' => $verifiedMandatoryCount,
            'uploaded_count' => $uploadedCount,
            'completion_percent' => $completionPercent,
            'is_ready_for_release' => $isReadyForRelease,
            'compliance_status' => $complianceStatus,
            'missing_mandatory' => $missingMandatory,
            'warnings' => $warnings,
        ];
    }

    /**
     * Check if a House Shipment is legally and operationally ready for Delivery Order release.
     */
    public function canReleaseDeliveryOrder(HouseShipment $houseShipment): array
    {
        // 1. Check Financial Hold
        if ($houseShipment->delivery_order_status === 'RELEASED') {
            return [
                'allowed' => false,
                'reason' => 'إذن التسليم صادر ومفرج عنه بالفعل.',
            ];
        }

        // 2. Check Master Shipment & House Documents Compliance
        $masterCompliance = $this->checkCompliance($houseShipment->masterShipment);
        if (! $masterCompliance['is_ready_for_release']) {
            $missingStr = implode('، ', array_slice($masterCompliance['missing_mandatory'], 0, 3));

            return [
                'allowed' => false,
                'reason' => "لا يمكن إصدار إذن التسليم قبل اعتماد المستندات الإلزامية للشحنة: ({$missingStr})",
            ];
        }

        // 3. Check customer invoices payment status if any exist
        $unpaidInvoices = $houseShipment->invoices()
            ->where('invoice_type', 'CUSTOMER_INVOICE')
            ->whereIn('status', ['DRAFT', 'POSTED', 'PARTIALLY_PAID'])
            ->exists();

        if ($unpaidInvoices) {
            return [
                'allowed' => false,
                'reason' => 'يوجد فواتير مستحقة على العميل لم تسدد بعد (Financial Hold Active).',
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
        ];
    }
}
