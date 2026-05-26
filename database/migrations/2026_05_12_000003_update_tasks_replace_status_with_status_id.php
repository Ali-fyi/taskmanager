<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // On retire l'ancienne colonne enum-string
            $table->dropColumn('status');

            // On ajoute la FK vers la nouvelle table statuses
            $table->foreignId('status_id')
                ->nullable()
                ->after('description')
                ->constrained('statuses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            $table->string('status')->default('todo')->after('description');
        });
    }
};
