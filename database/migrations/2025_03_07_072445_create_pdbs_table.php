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
        Schema::create('pdbs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('protein_id')->unsigned()->index();
            $table->string('pdb_id')->index();
            $table->timestamps();

            $table->foreign('protein_id')->references('id')->on('proteins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_d_b_s');
    }
};
