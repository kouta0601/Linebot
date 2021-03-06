<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabelogdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tabelogdata', function (Blueprint $table) {
            $table->bigIncrements('id');// IDを保存するカラム
            $table->string('url'); // URLを保存するカラム
            $table->string('name');  // 店名を保存するカラム
            $table->string('review'); // レビュー数を保存するカラム
            $table->string('type');  // ジャンルを保存するカラム
            $table->string('address');  // 住所を保存するカラム
            $table->timestamps();// タイムスタンプ
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabelogdata'); //テーブルがあれば削除
    }
}
