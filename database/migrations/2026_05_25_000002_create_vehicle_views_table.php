<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Placeholder schema — change or replace this however your approach requires.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_views', function (Blueprint $table) {
            $table->id();


            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('bucket_start')->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->unique(['vehicle_id', 'bucket_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_views');
    }
};
