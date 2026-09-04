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
        Schema::table('container_tracking_histories', function (Blueprint $table) {
            $table->string('tracking_type', 20)->default('container')->after('master_container_id');
            $table->string('container_number', 50)->nullable()->change();
            $table->string('vessel_imo', 50)->nullable()->after('vessel_name');
            $table->json('vessel_data')->nullable()->after('last_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_tracking_histories', function (Blueprint $table) {
            $table->dropColumn(['tracking_type', 'vessel_imo', 'vessel_data']);
            $table->string('container_number', 50)->nullable(false)->change();
        });
    }
};
