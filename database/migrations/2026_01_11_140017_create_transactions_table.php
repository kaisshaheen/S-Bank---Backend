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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->nullable()->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->cascadeOnDelete();
            $table->decimal('amount' , 15 , 2)->default(0);
            $table->enum('type' , ['deposit','withdraw','transfer']);
            $table->enum('status' , ['success','fail','pending'])->default('pending');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
