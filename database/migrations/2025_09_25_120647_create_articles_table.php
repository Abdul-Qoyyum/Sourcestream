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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->string('url');
            $table->text('image_url')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at');
            $table->json('source_metadata')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'external_id']);
            $table->index('published_at');
            $table->index(['source_id', 'published_at']);
            $table->index(['author']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
