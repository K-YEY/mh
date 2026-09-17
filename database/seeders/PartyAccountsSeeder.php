<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CustomsBroker;
use App\Models\Party;
use App\Models\PartyRole;
use App\Models\Trucker;
use Illuminate\Database\Seeder;

class PartyAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all()->keyBy('code');

        $parentClients = $accounts['11210'] ?? $accounts['11200'] ?? null;
        $parentCarriers = $accounts['21100'] ?? null;
        $parentCustoms = $accounts['21300'] ?? $accounts['21100'] ?? null;
        $parentTrucking = $accounts['21200'] ?? $accounts['21100'] ?? null;

        // 1. Setup Maersk Line
        $maersk = Party::where('code', 'LINE-MAERSK')->first();
        if ($maersk && ! $maersk->account_id && $parentCarriers) {
            $acc = Account::firstOrCreate(['code' => '21100-001'], [
                'name_ar' => 'حساب: توكيل خط ميرسك الملاحي (Maersk Line)',
                'name_en' => 'Maersk Line Shipping Line Account',
                'type' => 'LIABILITY',
                'parent_id' => $parentCarriers->id,
                'is_active' => true,
            ]);
            $maersk->update(['account_id' => $acc->id]);
        }

        // 2. Setup Hapag-Lloyd
        $hapag = Party::where('code', 'LINE-HAPAG')->first();
        if ($hapag && ! $hapag->account_id && $parentCarriers) {
            $acc = Account::firstOrCreate(['code' => '21100-002'], [
                'name_ar' => 'حساب: توكيل خط هاباج لويد (Hapag-Lloyd)',
                'name_en' => 'Hapag-Lloyd AG Carrier Account',
                'type' => 'LIABILITY',
                'parent_id' => $parentCarriers->id,
                'is_active' => true,
            ]);
            $hapag->update(['account_id' => $acc->id]);
        }

        // 3. Setup Delta Agri Export (Customer)
        $delta = Party::where('code', 'EXP-DELTA')->first();
        if ($delta && ! $delta->account_id && $parentClients) {
            $acc = Account::firstOrCreate(['code' => '11210-001'], [
                'name_ar' => 'حساب: شركة دلتا للحاصلات الزراعية والتصدير',
                'name_en' => 'Delta Agri Export Farms Account',
                'type' => 'ASSET',
                'parent_id' => $parentClients->id,
                'is_active' => true,
            ]);
            $delta->update(['account_id' => $acc->id]);
        }

        // 4. Setup EuroFresh (Customer)
        $euro = Party::where('code', 'IMP-EUROFRESH')->first();
        if ($euro && ! $euro->account_id && $parentClients) {
            $acc = Account::firstOrCreate(['code' => '11210-002'], [
                'name_ar' => 'حساب: شركة يوروفريش بروديوس (EuroFresh B.V.)',
                'name_en' => 'EuroFresh Produce B.V. Client Account',
                'type' => 'ASSET',
                'parent_id' => $parentClients->id,
                'is_active' => true,
            ]);
            $euro->update(['account_id' => $acc->id]);
        }

        // 5. Setup Alfa Cold-Pack (Packhouse)
        $pack = Party::where('code', 'PACK-ALFA')->first();
        if ($pack && ! $pack->account_id && $parentCarriers) {
            $acc = Account::firstOrCreate(['code' => '21100-003'], [
                'name_ar' => 'حساب: محطة ألفا للتبريد والتعبئة والتغليف',
                'name_en' => 'Alfa Cold-Pack Station Vendor Account',
                'type' => 'LIABILITY',
                'parent_id' => $parentCarriers->id,
                'is_active' => true,
            ]);
            $pack->update(['account_id' => $acc->id]);
        }

        // 6. Setup Example Customs Broker: CB-ALEX
        $cb = Party::firstOrCreate(['code' => 'CB-ALEX'], [
            'legal_name' => 'مكتب الإسكندرية الموحد للتخليص الجمركي',
            'trade_name' => 'Alexandria Customs Brokerage',
            'tax_number' => '409-221-554',
            'commercial_registry' => '994821',
            'email' => 'clearance@alexcustoms.eg',
            'phone' => '+2034871920',
            'address' => 'ميناء الإسكندرية، البوابة 27',
            'is_active' => true,
        ]);
        PartyRole::firstOrCreate(['party_id' => $cb->id, 'role' => 'CUSTOMS_BROKER']);

        if (! $cb->account_id && $parentCustoms) {
            $acc = Account::firstOrCreate(['code' => '21300-001'], [
                'name_ar' => 'حساب: مكتب الإسكندرية الموحد للتخليص الجمركي',
                'name_en' => 'Alexandria Customs Broker Account',
                'type' => 'LIABILITY',
                'parent_id' => $parentCustoms->id,
                'is_active' => true,
            ]);
            $cb->update(['account_id' => $acc->id]);
        }

        CustomsBroker::firstOrCreate(['code' => 'CB-ALEX'], [
            'party_id' => $cb->id,
            'name' => 'مكتب الإسكندرية الموحد للتخليص الجمركي',
            'trade_name' => 'Alexandria Customs Brokerage',
            'license_number' => 'EGY-CBRK-2024-089',
            'operating_ports' => 'EGALY, EGEDK, EGPSD',
            'tax_number' => '409-221-554',
            'commercial_registry' => '994821',
            'contact_person' => 'أ/ أحمد فؤاد',
            'contact_phone' => '+201004829104',
            'phone' => '+2034871920',
            'email' => 'clearance@alexcustoms.eg',
            'address' => 'ميناء الإسكندرية، البوابة 27',
            'is_active' => true,
        ]);

        // 7. Setup Example Trucker: TRK-NILE
        $trk = Party::firstOrCreate(['code' => 'TRK-NILE'], [
            'legal_name' => 'شركة النيل للشاحنات والتبريد اللوجستي',
            'trade_name' => 'Nile Reefer Trucking',
            'tax_number' => '518-339-102',
            'commercial_registry' => '102934',
            'email' => 'dispatch@niletrucks.com',
            'phone' => '+20227918400',
            'address' => 'طريق مصر الإسكندرية الصحراوي، كم 64',
            'is_active' => true,
        ]);
        PartyRole::firstOrCreate(['party_id' => $trk->id, 'role' => 'TRUCKER']);

        if (! $trk->account_id && $parentTrucking) {
            $acc = Account::firstOrCreate(['code' => '21200-001'], [
                'name_ar' => 'حساب: شركة النيل للشاحنات والتبريد اللوجستي',
                'name_en' => 'Nile Reefer Trucking Payables Account',
                'type' => 'LIABILITY',
                'parent_id' => $parentTrucking->id,
                'is_active' => true,
            ]);
            $trk->update(['account_id' => $acc->id]);
        }

        Trucker::firstOrCreate(['code' => 'TRK-NILE'], [
            'party_id' => $trk->id,
            'name' => 'شركة النيل للشاحنات والتبريد اللوجستي',
            'trade_name' => 'Nile Reefer Trucking',
            'fleet_size' => 45,
            'truck_types' => 'Reefer Trailer 40ft, ThermoKing GenSet',
            'coverage_areas' => 'All Egyptian Ports, Delta Packhouses, Upper Egypt Farms',
            'license_number' => 'TRK-MOT-99120',
            'tax_number' => '518-339-102',
            'commercial_registry' => '102934',
            'dispatch_contact' => 'ك/ محمود عبد السميع',
            'dispatch_phone' => '+201229048123',
            'phone' => '+20227918400',
            'email' => 'dispatch@niletrucks.com',
            'address' => 'طريق مصر الإسكندرية الصحراوي، كم 64',
            'is_active' => true,
        ]);
    }
}
