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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
             $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum ('nature', ['expense', 'income']);
            $table->foreignId('user_id')->onDelete('cascade');
            $table->foreignId('category_id')->onDelete('cascade');
            $table->foreignId('account_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
