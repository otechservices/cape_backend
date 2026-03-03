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
          Schema::table('billings', function (Blueprint $table) {
            $columns = ['name', 'email', 'token', 'is_open'];

    foreach ($columns as $column) {
        if (Schema::hasColumn('billings', $column)) {
            $table->dropColumn($column);
        }
    }

    if (!Schema::hasColumn('billings', 'priorite')) {
        $table->enum('priorite', ['Urgente','Normale','Faible'])
              ->default('Normale')
              ->after('id'); // adapte la position si nécessaire
    }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
