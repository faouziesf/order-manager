<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes avancées à WooCommerce
        if (Schema::hasTable('woo_commerce_settings')) {
            Schema::table('woo_commerce_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('woo_commerce_settings', 'first_sync_date')) {
                    $table->timestamp('first_sync_date')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('woo_commerce_settings', 'resync_day_of_week')) {
                    $table->integer('resync_day_of_week')->nullable()->after('first_sync_date');
                }
                if (!Schema::hasColumn('woo_commerce_settings', 'resync_time')) {
                    $table->time('resync_time')->default('02:00:00')->after('resync_day_of_week');
                }
            });
        }

        // Ajouter les colonnes avancées à Shopify
        if (Schema::hasTable('shopify_settings')) {
            Schema::table('shopify_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('shopify_settings', 'first_sync_date')) {
                    $table->timestamp('first_sync_date')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('shopify_settings', 'resync_day_of_week')) {
                    $table->integer('resync_day_of_week')->nullable()->after('first_sync_date');
                }
                if (!Schema::hasColumn('shopify_settings', 'resync_time')) {
                    $table->time('resync_time')->default('02:00:00')->after('resync_day_of_week');
                }
            });
        }

        // Ajouter les colonnes avancées à PrestaShop
        if (Schema::hasTable('prestashop_settings')) {
            Schema::table('prestashop_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('prestashop_settings', 'first_sync_date')) {
                    $table->timestamp('first_sync_date')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('prestashop_settings', 'resync_day_of_week')) {
                    $table->integer('resync_day_of_week')->nullable()->after('first_sync_date');
                }
                if (!Schema::hasColumn('prestashop_settings', 'resync_time')) {
                    $table->time('resync_time')->default('02:00:00')->after('resync_day_of_week');
                }
            });
        }
    }

    public function down(): void
    {
        // Suppression des colonnes
        if (Schema::hasTable('woo_commerce_settings')) {
            Schema::table('woo_commerce_settings', function (Blueprint $table) {
                $table->dropColumn(['first_sync_date', 'resync_day_of_week', 'resync_time']);
            });
        }

        if (Schema::hasTable('shopify_settings')) {
            Schema::table('shopify_settings', function (Blueprint $table) {
                $table->dropColumn(['first_sync_date', 'resync_day_of_week', 'resync_time']);
            });
        }

        if (Schema::hasTable('prestashop_settings')) {
            Schema::table('prestashop_settings', function (Blueprint $table) {
                $table->dropColumn(['first_sync_date', 'resync_day_of_week', 'resync_time']);
            });
        }
    }
};
