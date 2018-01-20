<?php

namespace App\Models\DB\api_lumen;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'mysql';  // 多数据库操作时最好进行绑定
    protected $table = 'users'; // 指定表
    protected $primaryKey = 'id'; // 指定主键
    public $timestamps = true;  // 是否自动维护时间戳
}