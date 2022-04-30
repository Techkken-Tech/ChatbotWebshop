<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateFixSpecialPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('product_flat', function (Blueprint $table) {
            $table->decimal('min_price', 12, 4)->nullable();
            $table->decimal('max_price', 12, 4)->nullable();
            //$table->decimal('special_price', 12, 4)->nullable()->change(); <-- Comentar essa linha
        });
        
        // Adicionar essa linha
        DB::statement('ALTER TABLE product_flat ALTER COLUMN special_price TYPE DECIMAL(12,4) USING CASE WHEN special_price = false THEN 0 ELSE 1 END;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_flat', function (Blueprint $table) {
            $table->dropColumn('min_price');
            $table->dropColumn('max_price');
        });
    }
}
