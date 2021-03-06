<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabelogdata extends Model
{
    //カラムに値を挿入
    public $timestamps = false; //timesatampを利用しない
    protected $fillable = ['url', 'name', 'review','type','address'];
}
