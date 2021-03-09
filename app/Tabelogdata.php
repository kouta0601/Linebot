<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabelogdata extends Model
{
    // テーブル名
    protected $table = 'tabelogdata';

    //カラムに値を挿入
    //false：timesatampを利用しない
    public $timestamps = false;
    protected $fillable = ['url', 'name', 'review', 'type', 'address'];

}
