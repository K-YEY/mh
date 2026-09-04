<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Document Sequences (Row-level Locking Counter Table)
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20);
            $table->integer('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->string('format', 100)->default('{PREFIX}-{YEAR}-{NUMBER:5}');
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['prefix', 'year'], 'uq_prefix_year');
        });

        // 2. Audit Trail Activity Logs
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name', 100)->default('default');
            $table->text('description');
            $table->string('subject_type', 150)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event', 50); // create, update, delete, post, void
            $table->string('causer_type', 150)->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->json('properties')->nullable(); // Original vs Changed state
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'idx_activity_subject');
            $table->index(['causer_type', 'causer_id'], 'idx_activity_causer');
        });

        // 3. User Role Column (if not existing)
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 50)->default('super-admin')->after('email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('document_sequences');

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
