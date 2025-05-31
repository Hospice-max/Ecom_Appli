<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la colonne user_id existe déjà
        $exists = DB::select("SHOW COLUMNS FROM products LIKE 'user_id'");

        if (empty($exists)) {
            DB::statement("ALTER TABLE products ADD COLUMN user_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vérifier si la colonne user_id existe
        $exists = DB::select("SHOW COLUMNS FROM products LIKE 'user_id'");

        if (!empty($exists)) {
            DB::statement("ALTER TABLE products DROP FOREIGN KEY products_user_id_foreign");
            DB::statement("ALTER TABLE products DROP COLUMN user_id");
        }
    }
};
