<?php

namespace Database\Seeders;

use App\Models\ShippingLine;
use Illuminate\Database\Seeder;

class ShippingLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = [
            [
                'code' => 'MSC',
                'scac' => 'MSCU',
                'name' => 'Mediterranean Shipping Company (MSC)',
                'name_ar' => 'شركة البحر الأبيض المتوسط للملاحة (MSC)',
                'bic_prefixes' => ['MSCU', 'MEDU', 'MSMU', 'TGHU', 'TCKU', 'GLDU', 'CXDU'],
                'tracking_url_template' => 'https://www.msc.com/en/track-a-shipment?reference={container}',
                'website_url' => 'https://www.msc.com',
                'logo_url' => 'https://www.msc.com/static-assets/msc-logo.svg',
            ],
            [
                'code' => 'MAERSK',
                'scac' => 'MAEU',
                'name' => 'Maersk Line',
                'name_ar' => 'ميرسك العالمية للملاحة (Maersk)',
                'bic_prefixes' => ['MAEU', 'MSKU', 'MRKU', 'SUDU', 'SEGU', 'PONU', 'MNBU'],
                'tracking_url_template' => 'https://www.maersk.com/tracking/{container}',
                'website_url' => 'https://www.maersk.com',
                'logo_url' => 'https://www.maersk.com/logo.svg',
            ],
            [
                'code' => 'CMA_CGM',
                'scac' => 'CMDU',
                'name' => 'CMA CGM Line',
                'name_ar' => 'سي إم إيه سي جي إم (CMA CGM)',
                'bic_prefixes' => ['CMAU', 'CGMU', 'ANXU', 'APLU', 'ECMU'],
                'tracking_url_template' => 'https://www.cma-cgm.com/ebusiness/tracking/search?SearchBy=Container&SearchValue={container}',
                'website_url' => 'https://www.cma-cgm.com',
                'logo_url' => 'https://www.cma-cgm.com/logo.svg',
            ],
            [
                'code' => 'HAPAG_LLOYD',
                'scac' => 'HLCU',
                'name' => 'Hapag-Lloyd',
                'name_ar' => 'هاباج لويد (Hapag-Lloyd)',
                'bic_prefixes' => ['HLCU', 'HLXU', 'CPPU', 'UASC', 'UASU', 'CLHU'],
                'tracking_url_template' => 'https://www.hapag-lloyd.com/en/online-business/track/track-by-container-solution.html?container={container}',
                'website_url' => 'https://www.hapag-lloyd.com',
                'logo_url' => 'https://www.hapag-lloyd.com/logo.svg',
            ],
            [
                'code' => 'COSCO',
                'scac' => 'COSU',
                'name' => 'COSCO Shipping Lines',
                'name_ar' => 'كوسكو الصينية للملاحة (COSCO)',
                'bic_prefixes' => ['COSU', 'CHSU', 'CBHU', 'CCLU', 'CSQU'],
                'tracking_url_template' => 'https://lines.coscoshipping.com/ebusiness/cargoTracking?trackingType=CONTAINER&trackingNumbers={container}',
                'website_url' => 'https://lines.coscoshipping.com',
                'logo_url' => 'https://lines.coscoshipping.com/logo.png',
            ],
            [
                'code' => 'OOCL',
                'scac' => 'OOLU',
                'name' => 'Orient Overseas Container Line (OOCL)',
                'name_ar' => 'أورينت أوفرسيز كونتينر لاين (OOCL)',
                'bic_prefixes' => ['OOCU', 'OOLU'],
                'tracking_url_template' => 'https://www.oocl.com/eng/ourservices/eservices/cargotracking/Pages/cargotracking.aspx?cargo_tracking_number={container}',
                'website_url' => 'https://www.oocl.com',
                'logo_url' => 'https://www.oocl.com/logo.png',
            ],
            [
                'code' => 'ONE',
                'scac' => 'ONEY',
                'name' => 'Ocean Network Express (ONE)',
                'name_ar' => 'أوشن نتورك إكسبريس (ONE)',
                'bic_prefixes' => ['ONEY', 'KKFU', 'NYKU', 'MOLU', 'KLFU', 'TRLU'],
                'tracking_url_template' => 'https://ecomm.one-line.com/one-ecomm/manage-shipment/cargo-tracking?trackingType=CONTAINER&trackingNumber={container}',
                'website_url' => 'https://www.one-line.com',
                'logo_url' => 'https://www.one-line.com/logo.png',
            ],
            [
                'code' => 'EVERGREEN',
                'scac' => 'EGLV',
                'name' => 'Evergreen Marine Corporation',
                'name_ar' => 'إيفرجرين مارين (Evergreen)',
                'bic_prefixes' => ['EGLV', 'EGHU', 'EISU', 'EMCU', 'UGMU'],
                'tracking_url_template' => 'https://ct.shipmentlink.com/servlet/TTrk_ConQuery?CNTR_NO={container}',
                'website_url' => 'https://www.evergreen-marine.com',
                'logo_url' => 'https://www.evergreen-marine.com/logo.png',
            ],
            [
                'code' => 'YANG_MING',
                'scac' => 'YMLU',
                'name' => 'Yang Ming Marine Transport',
                'name_ar' => 'يانج مينج للملاحة (Yang Ming)',
                'bic_prefixes' => ['YMLU', 'YMMU'],
                'tracking_url_template' => 'https://www.yangming.com/e-service/track_trace/track_trace_cargo_tracking.aspx?type=c&num={container}',
                'website_url' => 'https://www.yangming.com',
                'logo_url' => 'https://www.yangming.com/logo.png',
            ],
            [
                'code' => 'ZIM',
                'scac' => 'ZIMU',
                'name' => 'ZIM Integrated Shipping',
                'name_ar' => 'زيم للملاحة المتكاملة (ZIM)',
                'bic_prefixes' => ['ZIMU', 'ZCSU'],
                'tracking_url_template' => 'https://www.zim.com/tools/track-a-shipment?cons={container}',
                'website_url' => 'https://www.zim.com',
                'logo_url' => 'https://www.zim.com/logo.png',
            ],
            [
                'code' => 'HMM',
                'scac' => 'HDMU',
                'name' => 'HMM (Hyundai Merchant Marine)',
                'name_ar' => 'إتش إم إم هيونداي (HMM)',
                'bic_prefixes' => ['HDMU'],
                'tracking_url_template' => 'https://www.hmm21.com/cms/company/ebiz/track/track/index.jsp?number={container}',
                'website_url' => 'https://www.hmm21.com',
                'logo_url' => 'https://www.hmm21.com/logo.png',
            ],
            [
                'code' => 'PIL',
                'scac' => 'PILU',
                'name' => 'Pacific International Lines (PIL)',
                'name_ar' => 'باسيفيك إنترناشيونال لاينز (PIL)',
                'bic_prefixes' => ['PILU', 'PCIU'],
                'tracking_url_template' => 'https://www.pilship.com/en-our-track-and-trace/120.html?ref={container}',
                'website_url' => 'https://www.pilship.com',
                'logo_url' => 'https://www.pilship.com/logo.png',
            ],
            [
                'code' => 'WAN_HAI',
                'scac' => 'WHLU',
                'name' => 'Wan Hai Lines',
                'name_ar' => 'وان هاي لاينز (Wan Hai)',
                'bic_prefixes' => ['WHLU'],
                'tracking_url_template' => 'https://www.wanhai.com/views/cargo/cargo_tracking.xhtml?q_cargo_type=C&q_ref_no={container}',
                'website_url' => 'https://www.wanhai.com',
                'logo_url' => 'https://www.wanhai.com/logo.png',
            ],
            [
                'code' => 'ARKAS',
                'scac' => 'ARKU',
                'name' => 'Arkas Line',
                'name_ar' => 'أركاس لاين (Arkas)',
                'bic_prefixes' => ['ARKU'],
                'tracking_url_template' => 'https://www.arkasline.com.tr/en/tracking.html?cntr={container}',
                'website_url' => 'https://www.arkasline.com.tr',
                'logo_url' => 'https://www.arkasline.com.tr/logo.png',
            ],
            [
                'code' => 'TURKON',
                'scac' => 'TRKU',
                'name' => 'Turkon Line',
                'name_ar' => 'توركون لاين (Turkon)',
                'bic_prefixes' => ['TRKU'],
                'tracking_url_template' => 'https://www.turkon.com/en/tracking?container={container}',
                'website_url' => 'https://www.turkon.com',
                'logo_url' => 'https://www.turkon.com/logo.png',
            ],
        ];

        foreach ($lines as $line) {
            ShippingLine::updateOrCreate(
                ['code' => $line['code']],
                $line
            );
        }
    }
}
