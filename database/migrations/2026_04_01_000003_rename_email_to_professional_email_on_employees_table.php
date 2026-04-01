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
        if (! Schema::hasColumn('employees', 'email') || Schema::hasColumn('employees', 'professional_email')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('email', 'professional_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('employees', 'professional_email') || Schema::hasColumn('employees', 'email')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('professional_email', 'email');
        });
    }
};
