<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;

class WorldPortSeeder extends Seeder
{
    /**
     * Run database seeds for comprehensive global commercial seaports.
     */
    public function run(): void
    {
        $ports = [
            // ==========================================
            // 1. EGYPT (موانئ جمهورية مصر العربية)
            // ==========================================
            ['code' => 'EGALY', 'name' => 'Alexandria Port (ميناء الإسكندرية)', 'country_code' => 'EG'],
            ['code' => 'EGEDK', 'name' => 'El Dekheila Port (ميناء الدخيلة)', 'country_code' => 'EG'],
            ['code' => 'EGDAM', 'name' => 'Damietta Port (ميناء دمياط)', 'country_code' => 'EG'],
            ['code' => 'EGPSW', 'name' => 'Port Said West (ميناء غرب بورسعيد)', 'country_code' => 'EG'],
            ['code' => 'EGPSE', 'name' => 'Port Said East / SCCT (ميناء شرق بورسعيد)', 'country_code' => 'EG'],
            ['code' => 'EGSOK', 'name' => 'Ain Sokhna Port / DP World (ميناء العين السخنة)', 'country_code' => 'EG'],
            ['code' => 'EGADB', 'name' => 'Adabiya Port (ميناء الأدبية - السويس)', 'country_code' => 'EG'],
            ['code' => 'EGSUZ', 'name' => 'Port of Suez / Port Tawfiq (ميناء السويس - بور توفيق)', 'country_code' => 'EG'],
            ['code' => 'EGSGA', 'name' => 'Safaga Port (ميناء سفاجا البحري)', 'country_code' => 'EG'],
            ['code' => 'EGAIS', 'name' => 'Abu Qir Port (ميناء أبو قير الجديد)', 'country_code' => 'EG'],
            ['code' => 'EGNWB', 'name' => 'Nuweiba Port (ميناء نويبع البحري)', 'country_code' => 'EG'],

            // ==========================================
            // 2. ARAB GULF & MIDDLE EAST (الخليج العربي والشرق الأوسط)
            // ==========================================
            ['code' => 'SAJED', 'name' => 'Jeddah Islamic Port (ميناء جدة الإسلامي)', 'country_code' => 'SA'],
            ['code' => 'SADMM', 'name' => 'King Abdulaziz Port Dammam (ميناء الملك عبد العزيز بالدمام)', 'country_code' => 'SA'],
            ['code' => 'SAKAP', 'name' => 'King Abdullah Port (Rabigh) (ميناء الملك عبد الله - رابغ)', 'country_code' => 'SA'],
            ['code' => 'SAJUB', 'name' => 'Jubail Commercial Port (ميناء الجبيل التجاري)', 'country_code' => 'SA'],
            ['code' => 'SAYNB', 'name' => 'Yanbu Commercial Port (ميناء ينبع التجاري)', 'country_code' => 'SA'],
            ['code' => 'AEJEA', 'name' => 'Jebel Ali Port / DP World (ميناء جبل علي - دبي)', 'country_code' => 'AE'],
            ['code' => 'AEKHL', 'name' => 'Khalifa Port / AD Ports (ميناء خليفة - أبوظبي)', 'country_code' => 'AE'],
            ['code' => 'AESHJ', 'name' => 'Port Khalid (Sharjah) (ميناء خالد - الشارقة)', 'country_code' => 'AE'],
            ['code' => 'AEFJR', 'name' => 'Port of Fujairah (ميناء الفجيرة)', 'country_code' => 'AE'],
            ['code' => 'QAHMD', 'name' => 'Hamad Port (Doha) (ميناء حمد - الدوحة)', 'country_code' => 'QA'],
            ['code' => 'KWSIK', 'name' => 'Shuwaikh Port (ميناء الشويخ)', 'country_code' => 'KW'],
            ['code' => 'KWSBA', 'name' => 'Shuaiba Port (ميناء الشعيبة)', 'country_code' => 'KW'],
            ['code' => 'BHKBS', 'name' => 'Khalifa Bin Salman Port (ميناء خليفة بن سلمان)', 'country_code' => 'BH'],
            ['code' => 'OMSLL', 'name' => 'Port of Salalah (ميناء صلالة)', 'country_code' => 'OM'],
            ['code' => 'OMSOH', 'name' => 'Port of Sohar (ميناء صحار)', 'country_code' => 'OM'],
            ['code' => 'OMMCT', 'name' => 'Port Sultan Qaboos (Muscat) (ميناء السلطان قابوس)', 'country_code' => 'OM'],
            ['code' => 'JOAQB', 'name' => 'Aqaba Container Port (ميناء العقبة)', 'country_code' => 'JO'],
            ['code' => 'LBBEY', 'name' => 'Port of Beirut (ميناء بيروت)', 'country_code' => 'LB'],
            ['code' => 'LBTRP', 'name' => 'Port of Tripoli (ميناء طرابلس لبنان)', 'country_code' => 'LB'],
            ['code' => 'SYLAT', 'name' => 'Port of Latakia (ميناء اللاذقية)', 'country_code' => 'SY'],
            ['code' => 'LYBEN', 'name' => 'Port of Benghazi (ميناء بنغازي)', 'country_code' => 'LY'],
            ['code' => 'LYTIP', 'name' => 'Port of Tripoli (ميناء طرابلس ليبيا)', 'country_code' => 'LY'],
            ['code' => 'LYMRA', 'name' => 'Port of Misurata (ميناء مصراتة)', 'country_code' => 'LY'],
            ['code' => 'LYKHO', 'name' => 'Port of Khoms (ميناء الخمس)', 'country_code' => 'LY'],
            ['code' => 'TNRDS', 'name' => 'Port of Rades / Tunis (ميناء رادس - تونس)', 'country_code' => 'TN'],
            ['code' => 'TNSFA', 'name' => 'Port of Sfax (ميناء صفاقس)', 'country_code' => 'TN'],
            ['code' => 'DZALG', 'name' => 'Port of Algiers (ميناء الجزائر العاصمة)', 'country_code' => 'DZ'],
            ['code' => 'DZORN', 'name' => 'Port of Oran (ميناء وهران)', 'country_code' => 'DZ'],
            ['code' => 'DZBJA', 'name' => 'Port of Bejaia (ميناء بجاية)', 'country_code' => 'DZ'],
            ['code' => 'MACAS', 'name' => 'Port of Casablanca (ميناء الدار البيضاء)', 'country_code' => 'MA'],
            ['code' => 'MAPTM', 'name' => 'Tanger Med Port (ميناء طنجة المتوسط)', 'country_code' => 'MA'],
            ['code' => 'SDPZU', 'name' => 'Port Sudan (ميناء بورتسودان)', 'country_code' => 'SD'],
            ['code' => 'DJJIB', 'name' => 'Port of Djibouti (ميناء جيبوتي)', 'country_code' => 'DJ'],
            ['code' => 'YMADE', 'name' => 'Port of Aden (ميناء عدن)', 'country_code' => 'YE'],
            ['code' => 'YMHOD', 'name' => 'Port of Hodeidah (ميناء الحديدة)', 'country_code' => 'YE'],

            // ==========================================
            // 3. EUROPE & MEDITERRANEAN & BLACK SEA (أوروبا والبحر المتوسط والبحر الأسود)
            // ==========================================
            ['code' => 'NLRTM', 'name' => 'Port of Rotterdam (ميناء روتردام)', 'country_code' => 'NL'],
            ['code' => 'BEANR', 'name' => 'Port of Antwerp-Bruges (ميناء أنتويرب)', 'country_code' => 'BE'],
            ['code' => 'BEZEE', 'name' => 'Zeebrugge Port (ميناء زيبروج)', 'country_code' => 'BE'],
            ['code' => 'DEHAM', 'name' => 'Port of Hamburg (ميناء هامبورغ)', 'country_code' => 'DE'],
            ['code' => 'DEBRV', 'name' => 'Bremerhaven Port (ميناء بريمرهافن)', 'country_code' => 'DE'],
            ['code' => 'GBFXT', 'name' => 'Port of Felixstowe (ميناء فيلكستو)', 'country_code' => 'GB'],
            ['code' => 'GBSOU', 'name' => 'Port of Southampton (ميناء ساوثهامبتون)', 'country_code' => 'GB'],
            ['code' => 'GBLON', 'name' => 'London Gateway Port (ميناء لندن غيتواي)', 'country_code' => 'GB'],
            ['code' => 'GBLIV', 'name' => 'Port of Liverpool (ميناء ليفربول)', 'country_code' => 'GB'],
            ['code' => 'ESVLC', 'name' => 'Port of Valencia (ميناء فالنسيا)', 'country_code' => 'ES'],
            ['code' => 'ESALG', 'name' => 'Port of Algeciras (ميناء الجزيرة الخضراء)', 'country_code' => 'ES'],
            ['code' => 'ESBCN', 'name' => 'Port of Barcelona (ميناء برشلونة)', 'country_code' => 'ES'],
            ['code' => 'ESBIO', 'name' => 'Port of Bilbao (ميناء بلباو)', 'country_code' => 'ES'],
            ['code' => 'ITGOA', 'name' => 'Port of Genoa (ميناء جنوى)', 'country_code' => 'IT'],
            ['code' => 'ITGIT', 'name' => 'Port of Gioia Tauro (ميناء جويا تاورو)', 'country_code' => 'IT'],
            ['code' => 'ITTRS', 'name' => 'Port of Trieste (ميناء ترييستي)', 'country_code' => 'IT'],
            ['code' => 'ITSAL', 'name' => 'Port of Salerno (ميناء ساليرنو)', 'country_code' => 'IT'],
            ['code' => 'ITRAN', 'name' => 'Port of Ravenna (ميناء رافينا)', 'country_code' => 'IT'],
            ['code' => 'ITSPE', 'name' => 'Port of La Spezia (ميناء لا سبيتسيا)', 'country_code' => 'IT'],
            ['code' => 'ITLIV', 'name' => 'Port of Livorno (ميناء ليفورنو)', 'country_code' => 'IT'],
            ['code' => 'ITNAP', 'name' => 'Port of Naples (ميناء نابولي)', 'country_code' => 'IT'],
            ['code' => 'ITVCE', 'name' => 'Port of Venice (ميناء البندقية)', 'country_code' => 'IT'],
            ['code' => 'SIKOP', 'name' => 'Port of Koper (ميناء كوبر)', 'country_code' => 'SI'],
            ['code' => 'HRRIJ', 'name' => 'Port of Rijeka (ميناء رييكا)', 'country_code' => 'HR'],
            ['code' => 'GRPIR', 'name' => 'Port of Piraeus (ميناء بيرايوس)', 'country_code' => 'GR'],
            ['code' => 'GRSKG', 'name' => 'Port of Thessaloniki (ميناء سالونيك)', 'country_code' => 'GR'],
            ['code' => 'FRFOS', 'name' => 'Port of Marseille-Fos (ميناء مارسيليا / فوس)', 'country_code' => 'FR'],
            ['code' => 'FRLEH', 'name' => 'Port of Le Havre (ميناء لوهافر)', 'country_code' => 'FR'],
            ['code' => 'FRDKK', 'name' => 'Port of Dunkirk (ميناء دونكيرك)', 'country_code' => 'FR'],
            ['code' => 'TRIST', 'name' => 'Port of Istanbul (Ambarli) (ميناء إسطنبول - أمبارلي)', 'country_code' => 'TR'],
            ['code' => 'TRMER', 'name' => 'Port of Mersin (ميناء مرسين)', 'country_code' => 'TR'],
            ['code' => 'TRIZM', 'name' => 'Port of Izmir (Alsancak) (ميناء إزمير)', 'country_code' => 'TR'],
            ['code' => 'TRALI', 'name' => 'Aliaga Port / Nemrut (ميناء علي أغا)', 'country_code' => 'TR'],
            ['code' => 'TRGEB', 'name' => 'Gebze Port / Yilport (ميناء غبزة)', 'country_code' => 'TR'],
            ['code' => 'RUNVS', 'name' => 'Novorossiysk Port (ميناء نوفوروسيسك - روسيا)', 'country_code' => 'RU'],
            ['code' => 'RULED', 'name' => 'Saint Petersburg Port (ميناء سانت بطرسبرغ)', 'country_code' => 'RU'],
            ['code' => 'ROCND', 'name' => 'Port of Constanta (ميناء كونستانتسا)', 'country_code' => 'RO'],
            ['code' => 'BGVAR', 'name' => 'Port of Varna (ميناء فارنا)', 'country_code' => 'BG'],
            ['code' => 'PLGDN', 'name' => 'Port of Gdansk / Baltic Hub (ميناء غدانسك)', 'country_code' => 'PL'],
            ['code' => 'PLGDY', 'name' => 'Port of Gdynia (ميناء غدينيا)', 'country_code' => 'PL'],
            ['code' => 'SEGOT', 'name' => 'Port of Gothenburg (ميناء غوتنبرغ)', 'country_code' => 'SE'],
            ['code' => 'DKAAR', 'name' => 'Port of Aarhus (ميناء آرهوس)', 'country_code' => 'DK'],
            ['code' => 'CYLMS', 'name' => 'Port of Limassol (ميناء ليماسول)', 'country_code' => 'CY'],
            ['code' => 'MTMAR', 'name' => 'Marsaxlokk Malta Freeport (ميناء مالطا فري بورت)', 'country_code' => 'MT'],

            // ==========================================
            // 4. ASIA & FAR EAST & INDIA (آسيا والشرق الأقصى وشبه القارة الهندية)
            // ==========================================
            ['code' => 'CNSHA', 'name' => 'Port of Shanghai (ميناء شنغهاي)', 'country_code' => 'CN'],
            ['code' => 'CNNGB', 'name' => 'Ningbo-Zhoushan Port (ميناء نينغبو-تشوشان)', 'country_code' => 'CN'],
            ['code' => 'CNSZX', 'name' => 'Shenzhen Port (Yantian / Shekou) (ميناء شنتشن)', 'country_code' => 'CN'],
            ['code' => 'CNNSA', 'name' => 'Guangzhou Port (Nansha) (ميناء قوانغتشو - نانشا)', 'country_code' => 'CN'],
            ['code' => 'CNTAO', 'name' => 'Qingdao Port (ميناء تشينغداو)', 'country_code' => 'CN'],
            ['code' => 'CNTNG', 'name' => 'Tianjin Port (ميناء تيانجين)', 'country_code' => 'CN'],
            ['code' => 'CNXMN', 'name' => 'Xiamen Port (ميناء شيامن)', 'country_code' => 'CN'],
            ['code' => 'CNDLC', 'name' => 'Dalian Port (ميناء داليان)', 'country_code' => 'CN'],
            ['code' => 'HKHKG', 'name' => 'Port of Hong Kong (ميناء هونغ كونغ)', 'country_code' => 'HK'],
            ['code' => 'SGSIN', 'name' => 'Port of Singapore (ميناء سنغافورة)', 'country_code' => 'SG'],
            ['code' => 'MYPKG', 'name' => 'Port Klang (North / West Port) (ميناء كلانج)', 'country_code' => 'MY'],
            ['code' => 'MYTPP', 'name' => 'Port of Tanjung Pelepas (ميناء تانجونج بيليباس)', 'country_code' => 'MY'],
            ['code' => 'KRPUS', 'name' => 'Port of Busan (ميناء بوسان)', 'country_code' => 'KR'],
            ['code' => 'KRINC', 'name' => 'Port of Incheon (ميناء إنتشون)', 'country_code' => 'KR'],
            ['code' => 'JPTYO', 'name' => 'Port of Tokyo (ميناء طوكيو)', 'country_code' => 'JP'],
            ['code' => 'JPYOK', 'name' => 'Port of Yokohama (ميناء يوكوهاما)', 'country_code' => 'JP'],
            ['code' => 'JPUKB', 'name' => 'Port of Kobe (ميناء كوبي)', 'country_code' => 'JP'],
            ['code' => 'JPNGO', 'name' => 'Port of Nagoya (ميناء ناغويا)', 'country_code' => 'JP'],
            ['code' => 'TWKHH', 'name' => 'Port of Kaohsiung (ميناء كاوشيونغ)', 'country_code' => 'TW'],
            ['code' => 'TWKEL', 'name' => 'Port of Keelung (ميناء كيلونغ)', 'country_code' => 'TW'],
            ['code' => 'LKCMB', 'name' => 'Port of Colombo (ميناء كولومبو)', 'country_code' => 'LK'],
            ['code' => 'INNSA', 'name' => 'Nhava Sheva / JNPT Mumbai (ميناء نهافا شيفا - مومباي)', 'country_code' => 'IN'],
            ['code' => 'INMUN', 'name' => 'Mundra Port (Gujarat) (ميناء موندرا)', 'country_code' => 'IN'],
            ['code' => 'INMAA', 'name' => 'Chennai Port (Madras) (ميناء تشيناي)', 'country_code' => 'IN'],
            ['code' => 'INCOK', 'name' => 'Cochin Port (Vallarpadam) (ميناء كوتشين)', 'country_code' => 'IN'],
            ['code' => 'INPAV', 'name' => 'Port Pipavav (APM Terminals) (ميناء بيبافاف)', 'country_code' => 'IN'],
            ['code' => 'PKKHI', 'name' => 'Karachi Port (ميناء كراتشي)', 'country_code' => 'PK'],
            ['code' => 'PKBQM', 'name' => 'Port Muhammad Bin Qasim (ميناء قاسم)', 'country_code' => 'PK'],
            ['code' => 'BDCGP', 'name' => 'Chittagong Port (ميناء تشيتاغونغ)', 'country_code' => 'BD'],
            ['code' => 'THLCH', 'name' => 'Laem Chabang Port (ميناء لايم تشابانغ)', 'country_code' => 'TH'],
            ['code' => 'THBKK', 'name' => 'Bangkok Port (Klong Toey) (ميناء بانكوك)', 'country_code' => 'TH'],
            ['code' => 'VNSGN', 'name' => 'Ho Chi Minh City / Cat Lai (ميناء هو تشي منه)', 'country_code' => 'VN'],
            ['code' => 'VNCMP', 'name' => 'Cai Mep International Terminal (ميناء كاي ميب)', 'country_code' => 'VN'],
            ['code' => 'VNHPH', 'name' => 'Haiphong Port (ميناء هايفونغ)', 'country_code' => 'VN'],
            ['code' => 'IDTPP', 'name' => 'Tanjung Priok Port (Jakarta) (ميناء جاكرتا)', 'country_code' => 'ID'],
            ['code' => 'IDSUB', 'name' => 'Tanjung Perak Port (Surabaya) (ميناء سورابايا)', 'country_code' => 'ID'],
            ['code' => 'PHMNL', 'name' => 'Port of Manila (MICT) (ميناء مانيلا)', 'country_code' => 'PH'],

            // ==========================================
            // 5. NORTH & SOUTH AMERICA (أمريكا الشمالية واللاتينية)
            // ==========================================
            ['code' => 'USNYC', 'name' => 'Port of New York and New Jersey (ميناء نيويورك ونيوجيرسي)', 'country_code' => 'US'],
            ['code' => 'USLAX', 'name' => 'Port of Los Angeles (ميناء لوس أنجلوس)', 'country_code' => 'US'],
            ['code' => 'USLGB', 'name' => 'Port of Long Beach (ميناء لونغ بيتش)', 'country_code' => 'US'],
            ['code' => 'USSAV', 'name' => 'Port of Savannah (ميناء سافانا)', 'country_code' => 'US'],
            ['code' => 'USHOU', 'name' => 'Port of Houston (ميناء هيوستن)', 'country_code' => 'US'],
            ['code' => 'USORF', 'name' => 'Port of Virginia / Norfolk (ميناء فرجينيا / نورفولك)', 'country_code' => 'US'],
            ['code' => 'USCHS', 'name' => 'Port of Charleston (ميناء تشارلستون)', 'country_code' => 'US'],
            ['code' => 'USMIA', 'name' => 'PortMiami (ميناء ميامي)', 'country_code' => 'US'],
            ['code' => 'USOAK', 'name' => 'Port of Oakland (ميناء أوكلاند)', 'country_code' => 'US'],
            ['code' => 'USSEA', 'name' => 'Port of Seattle (ميناء سياتل)', 'country_code' => 'US'],
            ['code' => 'USTAC', 'name' => 'Port of Tacoma (ميناء تاكوما)', 'country_code' => 'US'],
            ['code' => 'CAVAN', 'name' => 'Port of Vancouver (ميناء فانكوفر)', 'country_code' => 'CA'],
            ['code' => 'CAMTR', 'name' => 'Port of Montreal (ميناء مونتريال)', 'country_code' => 'CA'],
            ['code' => 'CAPRR', 'name' => 'Prince Rupert Port (ميناء برنس روبرت)', 'country_code' => 'CA'],
            ['code' => 'BRSSZ', 'name' => 'Port of Santos (ميناء سانتوس - البرازيل)', 'country_code' => 'BR'],
            ['code' => 'BRPNG', 'name' => 'Port of Paranagua (ميناء باراناغوا)', 'country_code' => 'BR'],
            ['code' => 'BRRIO', 'name' => 'Port of Rio de Janeiro (ميناء ريو دي جانيرو)', 'country_code' => 'BR'],
            ['code' => 'ARBUE', 'name' => 'Port of Buenos Aires (ميناء بوينس آيرس)', 'country_code' => 'AR'],
            ['code' => 'PECLL', 'name' => 'Port of Callao (Lima) (ميناء كاياو - بيرو)', 'country_code' => 'PE'],
            ['code' => 'CLVAP', 'name' => 'Port of Valparaiso (ميناء فالبارايسو - تشيلي)', 'country_code' => 'CL'],
            ['code' => 'CLSAI', 'name' => 'Port of San Antonio (ميناء سان أنطونيو - تشيلي)', 'country_code' => 'CL'],
            ['code' => 'ECGYE', 'name' => 'Port of Guayaquil (ميناء غواياكيل - الإكوادور)', 'country_code' => 'EC'],
            ['code' => 'COCTG', 'name' => 'Port of Cartagena (ميناء قرطاجنة - كولومبيا)', 'country_code' => 'CO'],
            ['code' => 'MXZLO', 'name' => 'Port of Manzanillo (ميناء مانزانيلو - المكسيك)', 'country_code' => 'MX'],
            ['code' => 'MXVER', 'name' => 'Port of Veracruz (ميناء فيراكروز - المكسيك)', 'country_code' => 'MX'],
            ['code' => 'MXLZC', 'name' => 'Lazaro Cardenas Port (ميناء لازارو كارديناس)', 'country_code' => 'MX'],
            ['code' => 'PABLB', 'name' => 'Port of Balboa (ميناء بالبوا - بنما)', 'country_code' => 'PA'],
            ['code' => 'PAONX', 'name' => 'Manzanillo International Terminal (MIT Panama) (ميناء مانزانيلو - بنما)', 'country_code' => 'PA'],
            ['code' => 'JMKIN', 'name' => 'Kingston Container Terminal (ميناء كينغستون - جامايكا)', 'country_code' => 'JM'],
            ['code' => 'DOHAI', 'name' => 'Caucedo Port (DP World) (ميناء كاوكيدو - الدومينيكان)', 'country_code' => 'DO'],

            // ==========================================
            // 6. AFRICA & OCEANIA (إفريقيا وأوقيانوسيا)
            // ==========================================
            ['code' => 'ZADUR', 'name' => 'Port of Durban (ميناء ديربان - جنوب إفريقيا)', 'country_code' => 'ZA'],
            ['code' => 'ZACPT', 'name' => 'Port of Cape Town (ميناء كيب تاون)', 'country_code' => 'ZA'],
            ['code' => 'ZAPLZ', 'name' => 'Port Elizabeth / Coega (ميناء بورت إليزابيث)', 'country_code' => 'ZA'],
            ['code' => 'KEMBA', 'name' => 'Port of Mombasa (ميناء مومباسا - كينيا)', 'country_code' => 'KE'],
            ['code' => 'TZDAR', 'name' => 'Port of Dar es Salaam (ميناء دار السلام - تنزانيا)', 'country_code' => 'TZ'],
            ['code' => 'NGLOS', 'name' => 'Port of Lagos / Apapa (ميناء لاغوس / أبابا - نيجيريا)', 'country_code' => 'NG'],
            ['code' => 'GHTEM', 'name' => 'Port of Tema (ميناء تيما - غانا)', 'country_code' => 'GH'],
            ['code' => 'CIABJ', 'name' => 'Port of Abidjan (ميناء أبيدجان - كوت ديفوار)', 'country_code' => 'CI'],
            ['code' => 'SNDKR', 'name' => 'Port of Dakar (ميناء داكار - السنغال)', 'country_code' => 'SN'],
            ['code' => 'TGLOM', 'name' => 'Port of Lome (ميناء لومي - توغو)', 'country_code' => 'TG'],
            ['code' => 'AOLLD', 'name' => 'Port of Luanda (ميناء لواندا - أنغولا)', 'country_code' => 'AO'],
            ['code' => 'MZMPM', 'name' => 'Port of Maputo (ميناء مابوتو - موزمبيق)', 'country_code' => 'MZ'],
            ['code' => 'AUSYD', 'name' => 'Port Botany (Sydney) (ميناء سيدني - أستراليا)', 'country_code' => 'AU'],
            ['code' => 'AUMEL', 'name' => 'Port of Melbourne (ميناء ملبورن - أستراليا)', 'country_code' => 'AU'],
            ['code' => 'AUBNE', 'name' => 'Port of Brisbane (ميناء بريزبان - أستراليا)', 'country_code' => 'AU'],
            ['code' => 'AUFRE', 'name' => 'Fremantle Port (Perth) (ميناء فريمانتل - أستراليا)', 'country_code' => 'AU'],
            ['code' => 'NZAKL', 'name' => 'Ports of Auckland (ميناء أوكلاند - نيوزيلندا)', 'country_code' => 'NZ'],
            ['code' => 'NZTRG', 'name' => 'Port of Tauranga (ميناء تاورانجا - نيوزيلندا)', 'country_code' => 'NZ'],
        ];

        foreach ($ports as $portData) {
            Port::updateOrCreate(
                ['code' => $portData['code']],
                [
                    'name' => $portData['name'],
                    'country_code' => $portData['country_code'],
                    'is_active' => true,
                ]
            );
        }
    }
}
