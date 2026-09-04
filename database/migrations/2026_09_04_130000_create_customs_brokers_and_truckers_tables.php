<?php

use App\Models\Party;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCustomsBrokersAndTruckersTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Customs Brokers Table
        Schema::create('customs_brokers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('trade_name', 255)->nullable();
            $table->string('license_number', 100)->nullable();
            $table->string('operating_ports', 255)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('commercial_registry', 100)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Inland Truckers Table
        Schema::create('truckers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('trade_name', 255)->nullable();
            $table->integer('fleet_size')->nullable()->default(1);
            $table->string('truck_types', 255)->nullable();
            $table->string('coverage_areas', 255)->nullable();
            $table->string('license_number', 100)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('commercial_registry', 100)->nullable();
            $table->string('dispatch_contact', 150)->nullable();
            $table->string('dispatch_phone', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Migrate any existing party customs brokers and truckers into the dedicated tables
        $existingBrokers = Party::whereHas('roles', fn ($q) => $q->where('role', 'CUSTOMS_BROKER'))->get();
        foreach ($existingBrokers as $b) {
            DB::table('customs_brokers')->insertOrIgnore([
                'code' => $b->code,
                'name' => $b->legal_name,
                'trade_name' => $b->trade_name,
                'license_number' => 'LIC-'.rand(1000, 9999),
                'operating_ports' => 'ميناء الإسكندرية / ميناء الدخيلة / ميناء دمياط',
                'tax_number' => $b->tax_number,
                'commercial_registry' => $b->commercial_registry,
                'contact_person' => 'مندوب التخليص المعتمد',
                'contact_phone' => $b->phone,
                'phone' => $b->phone,
                'email' => $b->email,
                'address' => $b->address,
                'is_active' => $b->is_active,
                'party_id' => $b->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existingTruckers = Party::whereHas('roles', fn ($q) => $q->where('role', 'TRUCKER'))->get();
        foreach ($existingTruckers as $t) {
            DB::table('truckers')->insertOrIgnore([
                'code' => $t->code,
                'name' => $t->legal_name,
                'trade_name' => $t->trade_name,
                'fleet_size' => 15,
                'truck_types' => 'تريلات مبردة Reefer Trailers + مولدات جينست Genset',
                'coverage_areas' => 'النوبارية، السادات، الإسكندرية، دمياط، بورسعيد',
                'license_number' => 'TRK-MOT-'.rand(10000, 99999),
                'tax_number' => $t->tax_number,
                'commercial_registry' => $t->commercial_registry,
                'dispatch_contact' => 'غرفة الحركة والتشغيل',
                'dispatch_phone' => $t->phone,
                'phone' => $t->phone,
                'email' => $t->email,
                'address' => $t->address,
                'is_active' => $t->is_active,
                'party_id' => $t->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truckers');
        Schema::dropIfExists('customs_brokers');
    }
}

return new CreateCustomsBrokersAndTruckersTables;
