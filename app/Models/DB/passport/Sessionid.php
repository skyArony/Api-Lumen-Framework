<?php

namespace App\Models\DB\passport;

use Illuminate\Database\Eloquent\Model;

class Sessionid extends Model
{
    protected $connection = 'mysql-passport';  // 多数据库操作时最好进行绑定
    protected $table = 'sessionid'; // 指定表
    protected $primaryKey = 'id'; // 指定主键
    public $timestamps = true;  // 是否自动维护时间戳

    // 可更改字段的白名单
    // protected $fillable = ['item_key'];
    // 不可更改字段的黑名单
    // protected $guarded = ['price'];
}