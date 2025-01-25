<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRollbackLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('rollback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_version_id')->constrained('file_versions')->onDelete('cascade');
            $table->foreignId('rolled_back_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rolled_back_at');
            $table->text('note')->nullable(); // Optional note for rollback reason
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rollback_logs');
    }
}