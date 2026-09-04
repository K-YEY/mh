<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainerTrackingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('container_tracking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('master_container_id')->nullable()->constrained('master_containers')->nullOnDelete();
            $table->string('container_number', 50)->index();
            $table->string('shipping_line', 150)->nullable();
            $table->string('booking_reference', 100)->nullable();
            $table->string('master_bl_number', 100)->nullable();
            $table->string('vessel_name', 150)->nullable();
            $table->string('voyage_number', 50)->nullable();
            $table->string('pol_name', 150)->nullable();
            $table->string('pod_name', 150)->nullable();
            $table->string('current_status', 100)->default('IN_TRANSIT');
            $table->string('last_location', 150)->nullable();
            $table->timestamp('last_tracked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_tracking_histories');
    }
}

return new CreateContainerTrackingHistoriesTable;
