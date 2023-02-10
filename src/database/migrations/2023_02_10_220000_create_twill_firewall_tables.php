<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwillFirewallTables extends Migration
{
    public function up(): void
    {
        Schema::create('twill_firewall', function (Blueprint $table) {
            createDefaultTableFields($table);

            $table->string('domain')->nullable();

            $table->text('allow')->nullable();

            $table->text('block')->nullable();

            $table->string('redirect_to')->nullable();

            $table->boolean('allow_laravel_login')->default(false);

            $table->boolean('allow_twill_login')->default(false);
        });

        Schema::create('twill_firewall_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'twill_firewall', 'twill_firewall');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twill_firewall_revisions');
        Schema::dropIfExists('twill_firewall');
    }
}
