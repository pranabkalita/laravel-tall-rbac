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
            $table->bigInteger('protein_id')->unsigned()->index();
            $table->string('pmid')->nullable()->index();
            $table->text('title')->nullable();
            $table->string('published_on')->nullable();
            $table->string('last_revised_on')->nullable();
            $table->timestamps();

            $table->foreign('protein_id')->references('id')->on('proteins')->onDelete('cascade');

            // Define a FULLTEXT index directly on the 'title' column
            $table->fullText('title');
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
