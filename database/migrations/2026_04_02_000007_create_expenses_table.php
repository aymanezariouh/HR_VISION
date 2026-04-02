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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('category_id')
                ->constrained('expense_categories')
                ->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->string('receipt_path');
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending')->index();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
