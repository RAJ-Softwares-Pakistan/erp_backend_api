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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id('organization_id');
            $table->foreignId('root_user_id')->constrained('users');
            $table->string('name');
            $table->text('address');
            $table->string('phone');
            $table->string('email')->unique();
            $table->text('logo')->nullable();
            $table->string('website', 50)->nullable();
            $table->boolean('enable_gst')->default(false);
            $table->boolean('enable_witholding')->default(false);
            $table->string('ntn_no', 20)->nullable();
            $table->char('currency', 3)->default('PKR');
            $table->string('industry_type', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add organization_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained('organizations', 'organization_id');
        });

        // Add organization_id to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('organization_id')->constrained('organizations', 'organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::dropIfExists('organizations');
    }
};