<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('cache', fn (Blueprint $t) => [$t->string('key')->primary(), $t->mediumText('value'), $t->integer('expiration')]); Schema::create('cache_locks', fn (Blueprint $t) => [$t->string('key')->primary(), $t->string('owner'), $t->integer('expiration')]); }
    public function down(): void { Schema::dropIfExists('cache'); Schema::dropIfExists('cache_locks'); }
};
