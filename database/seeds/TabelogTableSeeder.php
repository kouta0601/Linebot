<?php

use Illuminate\Database\Seeder;

class TabelogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // csvファイル読み込み
        $file = new SplFileObject('./database/seeds/data.csv');
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );
        $list = [];
        foreach($file as $line) {
            $list[] = [
                'url' => $line[0],
                'name' => $line[1],
                'review' => $line[2],
                'type' => $line[3],
                'address' => $line[4]
            ];
        }
        // テーブルにデータ登録
        DB::table("tabelogdata")->insert($list);
    }
}
