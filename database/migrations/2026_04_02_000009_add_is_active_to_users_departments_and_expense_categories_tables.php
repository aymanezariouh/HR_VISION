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
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('password');
        });

        Schema::table('departments', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('name');
        });

        Schema::table('expense_categories', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });

        Schema::table('departments', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });

        Schema::table('expense_categories', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
