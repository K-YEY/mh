<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // 1. ASSETS (الأصول)
            [
                'code' => '10000',
                'name_ar' => 'الأصول',
                'name_en' => 'Assets',
                'type' => 'ASSET',
                'parent_code' => null,
            ],
            [
                'code' => '11000',
                'name_ar' => 'الأصول المتداولة',
                'name_en' => 'Current Assets',
                'type' => 'ASSET',
                'parent_code' => '10000',
            ],
            [
                'code' => '11100',
                'name_ar' => 'النقدية وما في حكمها والبنوك',
                'name_en' => 'Cash & Bank Balances',
                'type' => 'ASSET',
                'parent_code' => '11000',
            ],
            [
                'code' => '11110',
                'name_ar' => 'الخزينة الرئيسية (جنيه مصري)',
                'name_en' => 'Main Cash Safe (EGP)',
                'type' => 'ASSET',
                'parent_code' => '11100',
            ],
            [
                'code' => '11120',
                'name_ar' => 'الخزينة الرئيسية (دولار أمريكي)',
                'name_en' => 'Main Cash Safe (USD)',
                'type' => 'ASSET',
                'parent_code' => '11100',
            ],
            [
                'code' => '11130',
                'name_ar' => 'بنك مصر حساب جاري EGP (100064715498)',
                'name_en' => 'Banque Misr Current Account EGP (100064715498)',
                'type' => 'ASSET',
                'parent_code' => '11100',
            ],
            [
                'code' => '11140',
                'name_ar' => 'بنك مصر حساب جاري USD (100064715528)',
                'name_en' => 'Banque Misr Current Account USD (100064715528)',
                'type' => 'ASSET',
                'parent_code' => '11100',
            ],
            [
                'code' => '11200',
                'name_ar' => 'حسابات العملاء المجمعة (المدينون)',
                'name_en' => 'Accounts Receivable Control (Trade Debtors)',
                'type' => 'ASSET',
                'parent_code' => '11000',
            ],
            [
                'code' => '11210',
                'name_ar' => 'عملاء التصدير والشحن الخارجي',
                'name_en' => 'Foreign Export Clients',
                'type' => 'ASSET',
                'parent_code' => '11200',
            ],
            [
                'code' => '11220',
                'name_ar' => 'عملاء السوق المحلي والشحن الداخلي',
                'name_en' => 'Local Freight Clients',
                'type' => 'ASSET',
                'parent_code' => '11200',
            ],
            [
                'code' => '11300',
                'name_ar' => 'تأمينات الحاويات لدى الخطوط الملاحية',
                'name_en' => 'Shipping Line Container Deposits',
                'type' => 'ASSET',
                'parent_code' => '11000',
            ],
            [
                'code' => '11400',
                'name_ar' => 'عهد وسلف مكاتب التخليص الجمركي',
                'name_en' => 'Customs Broker Advances',
                'type' => 'ASSET',
                'parent_code' => '11000',
            ],
            [
                'code' => '11500',
                'name_ar' => 'عهد وسلف شركات النقل البري',
                'name_en' => 'Trucking Haulage Advances',
                'type' => 'ASSET',
                'parent_code' => '11000',
            ],
            [
                'code' => '12000',
                'name_ar' => 'الأصول الثابتة والمعدات',
                'name_en' => 'Fixed Assets & Office Equipment',
                'type' => 'ASSET',
                'parent_code' => '10000',
            ],

            // 2. LIABILITIES (الخصوم والالتزامات)
            [
                'code' => '20000',
                'name_ar' => 'الخصوم والالتزامات',
                'name_en' => 'Liabilities',
                'type' => 'LIABILITY',
                'parent_code' => null,
            ],
            [
                'code' => '21000',
                'name_ar' => 'الالتزامات المتداولة',
                'name_en' => 'Current Liabilities',
                'type' => 'LIABILITY',
                'parent_code' => '20000',
            ],
            [
                'code' => '21100',
                'name_ar' => 'حسابات الموردين وخطوط الشحن AP',
                'name_en' => 'Accounts Payable Control (Vendors & Ocean Lines)',
                'type' => 'LIABILITY',
                'parent_code' => '21000',
            ],
            [
                'code' => '21200',
                'name_ar' => 'مستحقات شركات النقل البري والمبرد',
                'name_en' => 'Trucking & Haulage Payables',
                'type' => 'LIABILITY',
                'parent_code' => '21000',
            ],
            [
                'code' => '21300',
                'name_ar' => 'مستحقات مكاتب التخليص الجمركي',
                'name_en' => 'Customs Brokerage Payables',
                'type' => 'LIABILITY',
                'parent_code' => '21000',
            ],
            [
                'code' => '21400',
                'name_ar' => 'تأمينات الحاويات المستردة للعملاء',
                'name_en' => 'Refundable Container Deposits to Clients',
                'type' => 'LIABILITY',
                'parent_code' => '21000',
            ],
            [
                'code' => '21500',
                'name_ar' => 'مستحقات مصلحة الضرائب المصرية',
                'name_en' => 'Taxes & Statutory Payables',
                'type' => 'LIABILITY',
                'parent_code' => '21000',
            ],

            // 3. EQUITY (حقوق الملكية)
            [
                'code' => '30000',
                'name_ar' => 'حقوق الملكية',
                'name_en' => 'Equity & Capital',
                'type' => 'EQUITY',
                'parent_code' => null,
            ],
            [
                'code' => '31000',
                'name_ar' => 'رأس المال المدفوع',
                'name_en' => 'Paid-in Capital',
                'type' => 'EQUITY',
                'parent_code' => '30000',
            ],
            [
                'code' => '32000',
                'name_ar' => 'الأرباح المرحلة والمحتجزة',
                'name_en' => 'Retained Earnings',
                'type' => 'EQUITY',
                'parent_code' => '30000',
            ],
            [
                'code' => '33000',
                'name_ar' => 'جاري الشركاء والمساهمين',
                'name_en' => 'Partners Current Account',
                'type' => 'EQUITY',
                'parent_code' => '30000',
            ],

            // 4. REVENUE (الإيرادات)
            [
                'code' => '40000',
                'name_ar' => 'الإيرادات التشغيلية والاستثنائية',
                'name_en' => 'Revenues',
                'type' => 'REVENUE',
                'parent_code' => null,
            ],
            [
                'code' => '41000',
                'name_ar' => 'إيرادات نشاط الشحن واللوجستيات',
                'name_en' => 'Freight & Logistics Revenues',
                'type' => 'REVENUE',
                'parent_code' => '40000',
            ],
            [
                'code' => '41100',
                'name_ar' => 'إيرادات نولون الشحن البحري',
                'name_en' => 'Ocean Freight Revenue',
                'type' => 'REVENUE',
                'parent_code' => '41000',
            ],
            [
                'code' => '41200',
                'name_ar' => 'إيرادات خدمات التخليص الجمركي',
                'name_en' => 'Customs Clearance Service Revenue',
                'type' => 'REVENUE',
                'parent_code' => '41000',
            ],
            [
                'code' => '41300',
                'name_ar' => 'إيرادات النقل البري والمشالات',
                'name_en' => 'Inland Haulage Service Revenue',
                'type' => 'REVENUE',
                'parent_code' => '41000',
            ],
            [
                'code' => '41400',
                'name_ar' => 'إيرادات شهادات وفحص الحجر الزراعي',
                'name_en' => 'Phytosanitary & Inspection Revenue',
                'type' => 'REVENUE',
                'parent_code' => '41000',
            ],
            [
                'code' => '41500',
                'name_ar' => 'إيرادات رسوم تداول وتفريغ الموانئ THC',
                'name_en' => 'Terminal Handling Revenue (THC)',
                'type' => 'REVENUE',
                'parent_code' => '41000',
            ],
            [
                'code' => '42000',
                'name_ar' => 'أرباح فروق أسعار العملات الأجنبية',
                'name_en' => 'Foreign Exchange Gain (FX Gain)',
                'type' => 'REVENUE',
                'parent_code' => '40000',
            ],

            // 5. EXPENSES & DIRECT COSTS (المصروفات وتكاليف التشغيل)
            [
                'code' => '50000',
                'name_ar' => 'المصروفات وتكاليف التشغيل',
                'name_en' => 'Expenses & Operational Costs',
                'type' => 'EXPENSE',
                'parent_code' => null,
            ],
            [
                'code' => '51000',
                'name_ar' => 'تكاليف العمليات المباشرة للشحن والتصدير',
                'name_en' => 'Direct Ocean Freight Operational Costs',
                'type' => 'EXPENSE',
                'parent_code' => '50000',
            ],
            [
                'code' => '51100',
                'name_ar' => 'تكاليف نولون الخطوط الملاحية المباشر',
                'name_en' => 'Ocean Line Direct Freight Expense',
                'type' => 'EXPENSE',
                'parent_code' => '51000',
            ],
            [
                'code' => '51200',
                'name_ar' => 'رسوم التداول والشحن والتفريغ بالموانئ THC',
                'name_en' => 'Terminal Handling Charges Expense (THC)',
                'type' => 'EXPENSE',
                'parent_code' => '51000',
            ],
            [
                'code' => '51300',
                'name_ar' => 'تكاليف النقل البري والشاحنات المبردة',
                'name_en' => 'Inland Reefer Trucking Expense',
                'type' => 'EXPENSE',
                'parent_code' => '51000',
            ],
            [
                'code' => '51400',
                'name_ar' => 'أتعاب ورسوم مكاتب التخليص الجمركي',
                'name_en' => 'Customs Clearance Direct Expense',
                'type' => 'EXPENSE',
                'parent_code' => '51000',
            ],
            [
                'code' => '51500',
                'name_ar' => 'رسوم سحب العينات وفحص معامل الحجر الزراعي',
                'name_en' => 'Phyto Lab & Sampling Analysis Fees',
                'type' => 'EXPENSE',
                'parent_code' => '51000',
            ],
            [
                'code' => '51600',
                'name_ar' => 'غرامات تأخير وأرضيات الموانئ (Demurrage)',
                'name_en' => 'Port Demurrage & Storage Expense',
                'type' => 'EXPENSE',
                'parent_code' => '51000',
            ],
            [
                'code' => '52000',
                'name_ar' => 'المصروفات العمومية والإدارية',
                'name_en' => 'General & Administrative Expenses (G&A)',
                'type' => 'EXPENSE',
                'parent_code' => '50000',
            ],
            [
                'code' => '52100',
                'name_ar' => 'الرواتب والأجور الشهرية',
                'name_en' => 'Salaries & Staff Wages',
                'type' => 'EXPENSE',
                'parent_code' => '52000',
            ],
            [
                'code' => '52200',
                'name_ar' => 'إيجار المقرات والمكاتب (فرع الإسكندرية)',
                'name_en' => 'Office Rent (Alexandria Branch)',
                'type' => 'EXPENSE',
                'parent_code' => '52000',
            ],
            [
                'code' => '52300',
                'name_ar' => 'مصاريف الاتصالات والإنترنت والبرمجيات',
                'name_en' => 'Telecom, Internet & IT Systems',
                'type' => 'EXPENSE',
                'parent_code' => '52000',
            ],
            [
                'code' => '52400',
                'name_ar' => 'خسائر فروق أسعار العملات الأجنبية',
                'name_en' => 'Foreign Exchange Loss (FX Loss)',
                'type' => 'EXPENSE',
                'parent_code' => '52000',
            ],
        ];

        // 1. First pass: insert or update accounts without parent_id
        foreach ($accounts as $acc) {
            Account::updateOrCreate(
                ['code' => $acc['code']],
                [
                    'name_ar' => $acc['name_ar'],
                    'name_en' => $acc['name_en'],
                    'type' => $acc['type'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Second pass: link parent_id
        $allAccounts = Account::all()->keyBy('code');
        foreach ($accounts as $acc) {
            if (! empty($acc['parent_code']) && isset($allAccounts[$acc['parent_code']])) {
                Account::where('code', $acc['code'])->update([
                    'parent_id' => $allAccounts[$acc['parent_code']]->id,
                ]);
            }
        }
    }
}
