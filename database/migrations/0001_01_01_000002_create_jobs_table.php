<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('jobs', function (Blueprint $t) { $t->id(); $t->string('queue')->index(); $t->longText('payload'); $t->unsignedTinyInteger('attempts'); $t->unsignedInteger('reserved_at')->nullable(); $t->unsignedInteger('available_at'); $t->unsignedInteger('created_at'); });
        Schema::create('job_batches', function (Blueprint $t) { $t->string('id')->primary(); $t->string('name'); $t->integer('total_jobs'); $t->integer('pending_jobs'); $t->integer('failed_jobs'); $t->longText('failed_job_ids'); $t->mediumText('options')->nullable(); $t->integer('cancelled_at')->nullable(); $t->integer('created_at'); $t->integer('finished_at')->nullable(); });
        Schema::create('failed_jobs', function (Blueprint $t) { $t->id(); $t->string('uuid')->unique(); $t->text('connection'); $t->text('queue'); $t->longText('payload'); $t->longText('exception'); $t->timestamp('failed_at')->useCurrent(); });
    }
    public function down(): void { Schema::dropIfExists('jobs'); Schema::dropIfExists('job_batches'); Schema::dropIfExists('failed_jobs'); }
};
