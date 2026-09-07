<?php

namespace Database\Seeders;

use App\Models\Party;
use App\Models\PartyRole;
use Illuminate\Database\Seeder;

class ShippingLineSeeder extends Seeder
{
    /**
     * Run seeds for the Top 10 World Shipping Lines and their primary agencies.
     */
    public function run(): void
    {
        $shippingLines = [
            [
                'code' => 'LINE-MSC',
                'legal_name' => 'MSC Mediterranean Shipping Company S.A.',
                'trade_name' => 'شركة إم إس سي للملاحة - وكالة مصر (MSC Agency Egypt)',
                'tax_number' => 'EG-200192831',
                'commercial_registry' => 'CR-MSC-ALEX-4410',
                'email' => 'egy-info@msc.com',
                'phone' => '+20 3 488 4000',
                'address' => 'الإسكندرية: شارع السلطان حسين / القاهرة: التسعين الشمالي، مجمع الملاحة',
            ],
            [
                'code' => 'LINE-MAERSK',
                'legal_name' => 'Maersk Line A/S (A.P. Moller - Maersk)',
                'trade_name' => 'توكيل ميرسك مصر للملاحة (Maersk Egypt Agency)',
                'tax_number' => 'DK53166911',
                'commercial_registry' => 'CR-MAERSK-9901',
                'email' => 'egycsclng@maersk.com',
                'phone' => '+20 2 2461 3000',
                'address' => 'القاهرة: نايل سيتي تاورز، كورنيش النيل / الإسكندرية: سموحة',
            ],
            [
                'code' => 'LINE-CMACGM',
                'legal_name' => 'CMA CGM S.A. (CMA CGM Group)',
                'trade_name' => 'سي إم إيه سي جي إم مصر للملاحة (CMA CGM Egypt)',
                'tax_number' => 'FR56562024422',
                'commercial_registry' => 'CR-CMA-EGY-3310',
                'email' => 'alex.genmbox@cma-cgm.com',
                'phone' => '+20 3 488 3300',
                'address' => 'الإسكندرية: شارع سوريا، كفر عبده / دمياط: المنطقة اللوجستية للميناء',
            ],
            [
                'code' => 'LINE-COSCO',
                'legal_name' => 'COSCO SHIPPING Lines Co., Ltd.',
                'trade_name' => 'شركة كوسكو للشحن والملاحة البحرية (COSCO Egypt Agency)',
                'tax_number' => 'CN91310000132205739',
                'commercial_registry' => 'CR-COSCO-5512',
                'email' => 'info@coscoegypt.com',
                'phone' => '+20 3 487 7750',
                'address' => 'الإسكندرية: المنشية، مبنى الغرفة التجارية / بورسعيد: شارع الجمهورية',
            ],
            [
                'code' => 'LINE-HAPAG',
                'legal_name' => 'Hapag-Lloyd AG',
                'trade_name' => 'هاباج لويد مصر للملاحة (Hapag-Lloyd Egypt)',
                'tax_number' => 'DE118617325',
                'commercial_registry' => 'CR-HLAG-1109',
                'email' => 'egypt@hlag.com',
                'phone' => '+20 3 488 6000',
                'address' => 'الإسكندرية: طريق الجيش، لوران / دمياط: مبنى الاستثمار بالميناء',
            ],
            [
                'code' => 'LINE-ONE',
                'legal_name' => 'Ocean Network Express Pte. Ltd. (ONE)',
                'trade_name' => 'أوشن نتورك إكسبريس مصر (ONE Agency Egypt)',
                'tax_number' => 'SG201718698Z',
                'commercial_registry' => 'CR-ONE-EGY-7819',
                'email' => 'egy.sales@one-line.com',
                'phone' => '+20 3 483 8810',
                'address' => 'الإسكندرية: شارع فؤاد، وسط البلد / بورسعيد: برج الملاحة',
            ],
            [
                'code' => 'LINE-EVERGREEN',
                'legal_name' => 'Evergreen Marine Corporation (Taiwan) Ltd.',
                'trade_name' => 'إيفرجرين لاين - التوكيل الملاحي (Evergreen Egypt Agencies)',
                'tax_number' => 'TW03077309',
                'commercial_registry' => 'CR-EVER-3341',
                'email' => 'egp@evergreen-marine.com',
                'phone' => '+20 3 486 2120',
                'address' => 'الإسكندرية: شارع السلطان حسين، الأزاريطة / القاهرة: مصر الجديدة',
            ],
            [
                'code' => 'LINE-HMM',
                'legal_name' => 'HMM Co., Ltd. (Hyundai Merchant Marine)',
                'trade_name' => 'إتش إم إم / هيونداي للملاحة البحرية (HMM Agency Egypt)',
                'tax_number' => 'KR1048111270',
                'commercial_registry' => 'CR-HMM-4491',
                'email' => 'egypt.sales@hmm21.com',
                'phone' => '+20 3 481 9912',
                'address' => 'الإسكندرية: شارع صلاح سالم، المنشية',
            ],
            [
                'code' => 'LINE-YANGMING',
                'legal_name' => 'Yang Ming Marine Transport Corp.',
                'trade_name' => 'يانغ مينغ لاين للملاحة (Yang Ming Agency Egypt)',
                'tax_number' => 'TW03212003',
                'commercial_registry' => 'CR-YM-9912',
                'email' => 'cs@yangming.com.eg',
                'phone' => '+20 3 484 0044',
                'address' => 'الإسكندرية: محطة الرمل، عمارة ميرامار / دمياط',
            ],
            [
                'code' => 'LINE-ZIM',
                'legal_name' => 'ZIM Integrated Shipping Services Ltd.',
                'trade_name' => 'زيم للملاحة والخدمات البحرية (ZIM Agency)',
                'tax_number' => 'IL520015049',
                'commercial_registry' => 'CR-ZIM-0982',
                'email' => 'egypt.info@zim.com',
                'phone' => '+20 3 482 1100',
                'address' => 'الإسكندرية: باب شرق، مجمع الملاحة الدولي',
            ],
            // Additional top reefer and Mediterranean carriers essential in Egyptian agricultural export
            [
                'code' => 'LINE-ARKAS',
                'legal_name' => 'Arkas Denizcilik ve Nakliyat A.S.',
                'trade_name' => 'أركاس إيجيبت للملاحة (Arkas Egypt Shipping Line)',
                'tax_number' => 'TR0790013912',
                'commercial_registry' => 'CR-ARKAS-8812',
                'email' => 'alexandria@arkas-egypt.com',
                'phone' => '+20 3 484 8400',
                'address' => 'الإسكندرية: كفر عبده / دمياط / بورسعيد - متخصص شحنات الموالح للبحر المتوسط والأسود',
            ],
            [
                'code' => 'LINE-TARROS',
                'legal_name' => 'Tarros S.p.A. (Tarros Line)',
                'trade_name' => 'تاروس إيجيبت للملاحة البحرية (Tarros Agency Egypt)',
                'tax_number' => 'IT00115040118',
                'commercial_registry' => 'CR-TARROS-1102',
                'email' => 'tarros.egypt@tarros.it',
                'phone' => '+20 3 480 3311',
                'address' => 'الإسكندرية: المنشية - خط منتظم للشحن المبرد إلى إيطاليا ودول جنوب أوروبا',
            ],
            [
                'code' => 'LINE-PIL',
                'legal_name' => 'Pacific International Lines (Pte) Ltd.',
                'trade_name' => 'بي آي إل للملاحة (PIL Egypt Agency)',
                'tax_number' => 'SG196700146G',
                'commercial_registry' => 'CR-PIL-7721',
                'email' => 'info@pil-egypt.com',
                'phone' => '+20 3 485 5500',
                'address' => 'الإسكندرية: شارع طلعت حرب / العين السخنة',
            ],
        ];

        foreach ($shippingLines as $lineData) {
            $party = Party::updateOrCreate(
                ['code' => $lineData['code']],
                [
                    'legal_name' => $lineData['legal_name'],
                    'trade_name' => $lineData['trade_name'],
                    'tax_number' => $lineData['tax_number'],
                    'commercial_registry' => $lineData['commercial_registry'],
                    'email' => $lineData['email'],
                    'phone' => $lineData['phone'],
                    'address' => $lineData['address'],
                    'is_active' => true,
                ]
            );

            PartyRole::firstOrCreate([
                'party_id' => $party->id,
                'role' => 'SHIPPING_LINE',
            ]);
        }
    }
}
