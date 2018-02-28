<?php

namespace App\Models\DB\passport;

use Illuminate\Database\Eloquent\Model;

class SysPortal extends Model
{
    protected $connection = 'mysql-passport';  // 多数据库操作时最好进行绑定
    protected $table = 'sys_portal'; // 指定表
    protected $primaryKey = 'sid'; // 指定主键
    public $timestamps = true;  // 是否自动维护时间戳

    /**
     * 模型的事件映射。
     *
     * @var array
     */
    // protected $dispatchesEvents = [
    //     'created' => SysPortalCreated::class,    // 填充passport_user_infos表中的信息
    // ];

    // 可更改字段的白名单
    // protected $fillable = ['item_key'];
    // 不可更改字段的黑名单
    // protected $guarded = ['price'];
}