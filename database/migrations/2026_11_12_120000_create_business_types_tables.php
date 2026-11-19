<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessTypesTables extends Migration
{
    public function up()
    {
        Schema::create('business_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_type_features', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_type_id');
            $table->string('feature_key', 100);
            $table->boolean('is_enabled')->default(true);
            $table->json('hidden_columns')->nullable();
            $table->boolean('hide_in_reports')->default(false);
            $table->boolean('hide_in_datatables')->default(false);
            $table->timestamps();
            $table->unique(['business_type_id', 'feature_key']);
            $table->foreign('business_type_id')->references('id')->on('business_types')->onDelete('cascade');
        });

        Schema::table('business', function (Blueprint $table) {
            $table->unsignedInteger('business_type_id')->nullable()->index()->after('id');
            $table->foreign('business_type_id')->references('id')->on('business_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->dropForeign(['business_type_id']);
            $table->dropColumn('business_type_id');
        });
        Schema::dropIfExists('business_type_features');
        Schema::dropIfExists('business_types');
    }
}
