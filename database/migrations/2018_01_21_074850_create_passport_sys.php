<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePassportSys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户表
        Schema::connection('mysql-passport')->create('passport_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sid')->unique()->comment('学号');
            $table->integer('status')->default(1)->comment('账号状态：1-正常；2-封禁；3-限制；具体请看定制的规则');
            $table->integer('type')->default(1)->comment('账号身份：1-学生老师大众；2-内部人员；3-开发人员；具体请看定制的规则');
            $table->ipAddress('last_ip')->comment('上次登录的IP地址');
            $table->nullableTimestamps('last_login_at')->comment('上次登录的时间');
            $table->timestamps();
        });

        // user_infos表，用户基本信息表
        Schema::connection('mysql-passport')->create('passport_user_infos', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('name')->comment('姓名');
            $table->string('class')->comment('班级');
            $table->string('major')->comment('专业');
            $table->string('college')->comment('学院');
            $table->string('img_url')->comment('照片地址');
            $table->string('sex')->comment('性别');
            $table->string('birth')->comment('出生日期');
            $table->string('home')->comment('家庭住址');
            $table->string('id_card')->comment('身份证号');
            $table->string('province')->comment('省份');
            $table->string('nationality')->comment('民族');
            $table->string('bedroom_build')->comment('寝室楼栋');
            $table->string('bedroom_floor')->comment('寝室楼层');
            $table->string('bedroom_id')->comment('寝室号');
            $table->string('bedroom_bedid')->comment('床位号');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // sessionId 储存表
        Schema::connection('mysql-passport')->create('sessionid', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sid')->comment('学号');
            $table->string('edu')->comment('教务系统');
            $table->string('ecard')->comment('一卡通');
            $table->string('library')->comment('图书馆');
            $table->string('portal')->comment('信息门户');
            $table->string('repair')->comment('报修系统');
            $table->timestamps();
        });

        // 教务系统验证码信息存储表
        Schema::connection('mysql-passport')->create('idcode_edu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('str')->comment('代表字符');
            $table->mediumText('bits')->comment('字符信息');
        });

        // 信息门户验证码信息存储表
        Schema::connection('mysql-passport')->create('idcode_portal', function (Blueprint $table) {
            $table->increments('id');
            $table->string('str')->comment('代表字符');
            $table->mediumText('bits')->comment('字符信息');
        });

        // sys_edu表，用户教务系统信息表
        Schema::connection('mysql-passport')->create('sys_edu', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('password', 3000)->comment('教务密码');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // sys_portal表，用户信息门户系统信息表
        Schema::connection('mysql-passport')->create('sys_portal', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('password', 3000)->comment('用户信息门户密码');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // sys_ecard表，用户一卡通系统信息表
        Schema::connection('mysql-passport')->create('sys_ecard', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('password', 3000)->comment('用户一卡通密码');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // sys_library表，用户图书馆系统信息表
        Schema::connection('mysql-passport')->create('sys_library', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('password', 3000)->comment('用户图书馆密码');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // sys_repair表，用户报修系统系统信息表
        Schema::connection('mysql-passport')->create('sys_repair', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('password', 3000)->comment('用户报修系统密码');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // third_gonggong表，拱拱用户信息表：这里较拱拱1.0，course_share改成了course_befollow，以前是存储公开的课表数据，现在是存储和follow一样的格式
        // Schema::connection('mysql-passport')->create('third_gonggong', function (Blueprint $table) {
        //     $table->string('sid')->comment('学号');
        //     $table->string('name')->comment('姓名');
        //     $table->string('sex')->comment('性别');
        //     $table->string('nickname')->comment('昵称');
        //     $table->text('img')->comment('头像地址');
        //     $table->text('setting')->comment('客户端保存的设置');
        //     $table->text('timer')->comment('用户自定义的倒计时');
        //     $table->text('course')->comment('用户自定义的课程');
        //     $table->text('course_follow')->comment('你关注的人');
        //     $table->text('course_befollow')->comment('关注你的人');
        //     $table->text('course_share_code')->comment('课表关联码');
        //     $table->text('subscribe')->comment('发现订阅的栏目，包括id和排序（九宫格的排序）');
        //     $table->text('radio_like')->comment('电台的“喜欢”标记'); // 以前是radio
        //     $table->ipAddress('last_ip')->comment('上次登录的IP地址');
        //     $table->timestamp('last_date')->comment('上次登录的时间');
        //     $table->string('last_plat')->comment('上次登录的平台');
        //     $table->timestamps();
        //     $table->primary('sid');
        //     $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        // });

        // 暂未针对性进行开发，表结构暂定

        // third_qq表，第三方表：用户关联的QQ
        Schema::connection('mysql-passport')->create('third_qq', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('qq')->comment('QQ号');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // third_wechat表，第三方表：用户关联的微信
        Schema::connection('mysql-passport')->create('third_wechat_isky31_gzh', function (Blueprint $table) {
            $table->string('sid')->comment('学号');
            $table->string('wechat')->comment('微信号');
            $table->timestamps();
            $table->primary('sid');
            $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        });

        // third_weibo表，第三方表：用户关联的weibo
        // Schema::connection('mysql-passport')->create('third_weibo', function (Blueprint $table) {
        //     $table->string('sid')->comment('学号');
        //     $table->string('weibo')->comment('微博');
        //     $table->timestamps();
        //     $table->primary('sid');
        //     $table->foreign('sid')->references('sid')->on('passport_users')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
