<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('news', function (Blueprint $t) { $t->id(); $t->string('title'); $t->string('slug')->unique(); $t->text('excerpt')->nullable(); $t->longText('body'); $t->string('image')->nullable(); $t->timestamp('published_at')->nullable()->index(); $t->boolean('is_published')->default(false)->index(); $t->timestamps(); $t->softDeletes(); }); }
    public function down(): void { Schema::dropIfExists('news'); }
};
