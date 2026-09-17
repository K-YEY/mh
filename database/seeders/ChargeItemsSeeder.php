<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ChargeItem;
use Illuminate\Database\Seeder;

class ChargeItemsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all()->keyBy('code');

        $revOcean = $accounts['41100']->id ?? 4;
        $expOcean = $accounts['51100']->id ?? 5;

        $revThc = $accounts['41500']->id ?? $revOcean;
        $expThc = $accounts['51200']->id ?? $expOcean;

        $revCustoms = $accounts['41200']->id ?? $revOcean;
        $expCustoms = $accounts['51400']->id ?? $expOcean;

        $revTrucking = $accounts['41300']->id ?? $revOcean;
        $expTrucking = $accounts['51300']->id ?? $expOcean;

        $revPhyto = $accounts['41400']->id ?? $revOcean;
        $expPhyto = $accounts['51500']->id ?? $expOcean;

        $expDemurrage = $accounts['51600']->id ?? $expOcean;
        $expAdmin = $accounts['52000']->id ?? $expOcean;

        $items = [
            [
                'code' => 'OFRT',
                'name_ar' => 'نولون شحن بحري مبرد (Ocean Freight)',
                'name_en' => 'Ocean Freight Reefer',
                'revenue_account_id' => $revOcean,
                'expense_account_id' => $expOcean,
            ],
            [
                'code' => 'THC',
                'name_ar' => 'عوائد ورسوم تفريغ وتداول بالحاوية (THC)',
                'name_en' => 'Terminal Handling Charges (THC)',
                'revenue_account_id' => $revThc,
                'expense_account_id' => $expThc,
            ],
            [
                'code' => 'CUSTOMS',
                'name_ar' => 'أتعاب وخدمات التخليص الجمركي (Customs Clearance)',
                'name_en' => 'Customs Clearance & Brokerage',
                'revenue_account_id' => $revCustoms,
                'expense_account_id' => $expCustoms,
            ],
            [
                'code' => 'TRUCKING',
                'name_ar' => 'شحن ونقل بري حاويات مبردة (Inland Haulage)',
                'name_en' => 'Inland Reefer Trucking',
                'revenue_account_id' => $revTrucking,
                'expense_account_id' => $expTrucking,
            ],
            [
                'code' => 'PHYTO',
                'name_ar' => 'فحص ومعاينة وسحب عينات حجر زراعي (Phytosanitary)',
                'name_en' => 'Phyto Inspection & Sampling',
                'revenue_account_id' => $revPhyto,
                'expense_account_id' => $expPhyto,
            ],
            [
                'code' => 'DEMURRAGE',
                'name_ar' => 'غرامات تأخير وأرضيات موانئ (Demurrage & Storage)',
                'name_en' => 'Port Demurrage & Detention',
                'revenue_account_id' => $revThc,
                'expense_account_id' => $expDemurrage,
            ],
            [
                'code' => 'DOC_FEE',
                'name_ar' => 'مصاريف إصدار بوالص الشحن وإذن التسليم (D/O & BL Fees)',
                'name_en' => 'Documentation & BL Issuance Fees',
                'revenue_account_id' => $revCustoms,
                'expense_account_id' => $expAdmin,
            ],
            [
                'code' => 'SEAL_FEE',
                'name_ar' => 'رسوم ترصيص وسيل الحاويات (Container Seal)',
                'name_en' => 'Container Seal Fees',
                'revenue_account_id' => $revThc,
                'expense_account_id' => $expThc,
            ],
        ];

        foreach ($items as $item) {
            ChargeItem::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name_ar' => $item['name_ar'],
                    'name_en' => $item['name_en'],
                    'revenue_account_id' => $item['revenue_account_id'],
                    'expense_account_id' => $item['expense_account_id'],
                ]
            );
        }
    }
}
