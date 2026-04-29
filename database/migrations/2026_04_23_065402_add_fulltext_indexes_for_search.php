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
        Schema::table('stores', function (Blueprint $table) {
            $table->fullText('name');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->fullText('name');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->fullText('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->fullText(['name', 'description']);
        });

        Schema::table('product_tags', function (Blueprint $table) {
            $table->fullText('tag_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropFullText(['name']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropFullText(['name']);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropFullText(['name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropFullText(['name', 'description']);
        });

        Schema::table('product_tags', function (Blueprint $table) {
            $table->dropFullText(['tag_name']);
        });
    }
};
