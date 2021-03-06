<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         // テーブルにデータの流し込みを呼び出す
        $this->call(TabelogTableSeeder::class);
    }
}
