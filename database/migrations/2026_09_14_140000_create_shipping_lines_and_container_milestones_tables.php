<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Shipping Lines Registry with BIC prefixes and tracking URLs
        if (! Schema::hasTable('shipping_lines')) {
            Schema::create('shipping_lines', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('scac', 10)->nullable()->index();
                $table->string('name', 150);
                $table->string('name_ar', 150)->nullable();
                $table->json('bic_prefixes')->nullable();
                $table->string('tracking_url_template', 500);
                $table->string('website_url', 255)->nullable();
                $table->string('logo_url', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Container Milestones Timeline
        if (! Schema::hasTable('container_milestones')) {
            Schema::create('container_milestones', function (Blueprint $table) {
                $table->id();
                $table->string('container_number', 20)->index();
                $table->foreignId('master_container_id')->nullable()->constrained('master_containers')->nullOnDelete();
                $table->foreignId('shipping_line_id')->nullable()->constrained('shipping_lines')->nullOnDelete();

                $table->string('milestone_code', 50)->index();
                $table->string('milestone_name', 150);
                $table->string('milestone_name_ar', 150)->nullable();
                $table->string('location_name', 150)->nullable();
                $table->string('vessel_name', 150)->nullable();
                $table->string('voyage_number', 50)->nullable();

                $table->timestamp('occurred_at')->nullable();
                $table->boolean('is_completed')->default(true);
                $table->string('source', 50)->default('MANUAL'); // SEARATES, SHIPSGO, CARRIER_API, MANUAL, SYSTEM
                $table->json('raw_payload')->nullable();
                $table->timestamps();

                $table->index(['container_number', 'occurred_at']);
            });
        }

        // 3. Extend Master Containers with ISO & tracking metadata
        Schema::table('master_containers', function (Blueprint $table) {
            if (! Schema::hasColumn('master_containers', 'carrier_code')) {
                $table->string('carrier_code', 50)->nullable()->after('container_number');
            }
            if (! Schema::hasColumn('master_containers', 'carrier_deep_link')) {
                $table->string('carrier_deep_link', 500)->nullable()->after('carrier_code');
            }
            if (! Schema::hasColumn('master_containers', 'iso_valid')) {
                $table->boolean('iso_valid')->default(false)->after('carrier_deep_link');
            }
            if (! Schema::hasColumn('master_containers', 'eta')) {
                $table->timestamp('eta')->nullable()->after('iso_valid');
            }
            if (! Schema::hasColumn('master_containers', 'etd')) {
                $table->timestamp('etd')->nullable()->after('eta');
            }
            if (! Schema::hasColumn('master_containers', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('etd');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_containers', function (Blueprint $table) {
            $table->dropColumn([
                'carrier_code',
                'carrier_deep_link',
                'iso_valid',
                'eta',
                'etd',
                'last_sync_at',
            ]);
        });

        Schema::dropIfExists('container_milestones');
        Schema::dropIfExists('shipping_lines');
    }
};
