# 介绍
**Api-Lumen-Framework** 是一个用 **Lumen** 框架构建的**底层api基础框架**，你可以基于此框架快速构建你的 api。

后面的版本是搭建在前面的版本基础上的，例如 **V1.0** 仅有 **Api控制系统** ，**V1.1** 则是在前面的基础上再搭建了一个 **passport统一登录系统**。

# V1.0.0 (api 控制系统)
基本底层框架，实现了一个可以对api进行设定和权限分配的系统。

## 一、环境和功能介绍

#### 用到的框架及其版本
- Lumen v5.5
- dingo v2.0.0-alpha1
- JWT v1.0.0-rc.1

#### 功能
- JWT 权限认证
- 灵活的权限控制
- api 调用统计
- api 调用速率限制

#### 环境要求
- composer
- git

## 二、如何开始

### 1. 下载到本地
执行 `git clone` 下载到本地。

### 2. 更新 composer 包
执行 `composer update` ，更新 composer 包。

### 3. 新建配置文件
在根目录新建你的 `.env` 文件

**.env 示例文件**

```
APP_ENV=local
APP_DEBUG=true          // 生产环境设置为false
APP_KEY=base64:Dje+SgXpfHHxCuelIzeTnxtmcHZmoVCXk/PLoefUOW8=     // 设置你自己的key
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_lumen   // 在数据库新建你的数据库，数据库名字写在这
DB_USERNAME=root        // 数据库账号
DB_PASSWORD=root        // 数据库密码

CACHE_DRIVER=file
QUEUE_DRIVER=sync

API_STANDARDS_TREE=x    // x 本地或私有环境 prs 非商业销售的项目 vnd 公开的以及商业销售的项目
API_SUBTYPE=skyapi      // API简称
API_NAME=MyAPI          // API名称
API_VERSION=v1          // API默认版本
API_PREFIX=api          // API前缀，会体现在 URL 中 或使用API_DOMAIN - API子域名的形式，二选一
API_CONDITIONAL_REQUEST=false   // 带条件的请求，由于缓存API请求的时候会使用客户端缓存功能，所以默认开启了带条件的请求
API_STRICT=false        // 严格模式，要求客户端发送Accept头而不是默认在配置文件中指定的版本
API_DEFAULT_FORMAT=json     // 响应格式，默认的响应格式是JSON
API_DEBUG=true          // 调试模式

JWT_SECRET=7tXjsx0Y4CjkF5kKYvPXu884qxuT1w9b     // 用php artisan jwt:secret生成
```

### 4. 运行数据库迁移 
运行数据库迁移之前，先在数据库建立 `api_lumen` 和 `passport` 两个数据库，然后

```
php artisan migrate     // 运行迁移生成数据表
```

### 5. 填充几条测试数据
这个命令会在 `users` 表中填充一条测试数据，每运行一次填充一条。

```
composer dump-autoload  // 重新生成composer的自动加载器

php artisan db:seed // 填充一条测试数据（user），运行一次填充一条
```

## 三、如何使用

### 1. 数据库表结构大致如下：
**权限分配**的 E-R 图如下：

![ E-R 图](http://osv9x79o9.bkt.clouddn.com/18-1-16/68028673.jpg)

- items 中是一条一条的 API
- groups 中是一个一个 API 组，每个组可以包含任意条 API
- collections 中是一个一个的 API 集合，每个集合可以包含任意条 API 和任意个 groups
- users 是这个系统的用户（开发者、系统等），每个用户可以用有任意个 API 集合，即拥有集合中 API 的使用权限

操纵以上关系的路由是**权限分配路由**，为了方便暂未将其加入路由保护。

### 2. 设定路由
在 **\routes\web.php** 中设定你的路由：

```php
$api->version('v1', ['namespace' => 'App\Http\Controllers'], function ($api) {
  // 认证部分：获取token
  $api->get('/auth/token', 'AuthController@createToken');
  // 认证部分：刷新token
  $api->patch('/auth/token', 'AuthController@refreshToken');
  // 认证部分：删除token
  $api->delete('/auth/token', 'AuthController@deleteToken');

  // jwt-auth的路由保护，放在这里面的就需要带上token访问，否则可以绕过权限直接访问
  $api->group(['middleware' => ['auth', 'api.permission', 'api.timeslimit', 'api.timescounter']], function ($api) {
      // 资源获取：users
      $api->resource('/users', 'UserController');
  });
});
```
**权限分配路由**暂未设置认证保护，你可以后面自己加入保护中。

### 3. 获取一个 token
用前面 `seed` 生成的 `User` 来获取一个 `token`。

```
GET：/auth/token

email：xxxx@gmail.com
password：secret （填充的测试数据，这里默认为secret）
```

### 4. 注册 API （这个是一个权限分配路由，后面类似的略）
用以下这条路由注册一条 API

```
POST：/perm/item

key：USRE-GetAllUser
intro：获取所有的用户
url：http://127.0.0.1:8003/api/users
method：GET
[token：在你把这条路由加入保护后，你需要输入这个 token 参数才能正常使用这条路由]
```

### 5. 创建一个 Collection

```
POST：/perm/collection

key：Collection-1
intro：第一个测试集合
istemp：boolean （可选，默认true）
```

### 6. 分配刚刚注册的 API 到 Collection

```
POST：/perm/contact

container：Collection-1
element：["USRE-GetAllUser"]  // json数组
type：ci   // 可选 ci、cu、cg、gi，分别表示建立 collection 和 item，collection 和 user(email)， colletion 和 group，group 和 item 的联系
```

### 7. 把 Collection 分配给 User

```
POST: /perm/contact

type：cu
container：Collection-1
element：["xxxx@gmail.com"] 
```

### 8. 设置用户的接口可调用次数

```
PATCH：/perm/left-times

email：xxxx@gmail.com
times：1000
```

### 9. 附带 token 访问数据

```
GET：/users

token：eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDMvYXBpL2F1dGgvdG9rZW4iLCJpYXQiOjE1MTY0MzMyNjMsImV4cCI6MTUxNjQ0MDQ2MywibmJmIjoxNTE2NDMzMjYzLCJqdGkiOiJVcGFvRkxYQXRSY1lxRGRiIiwic3ViIjoxLCJwcnYiOiI4N2UwYWYxZWY5ZmQxNTgxMmZkZWM5NzE1M2ExNGUwYjA0NzU0NmFhIn0.1RAaVARi1qj1evvGxEiaCeP1Z-hDsBTRz2p1YLbC9GM
```

由于这条路由在路由文件中加入了 `auth` 中间件的保护，所以需要附带 `token` 才能通过中间件获取到数据。


## 四、其他

### 1. token 的设置（有效时间，刷新时间，宽限时间）
`config/jwt.php`

### 2. 客户端如何维护 token
`token` 的第二段 `base64` 解码可得到有效时间，在过期时 `PATCH` 请求 `/auth/token` 更新 `token`。

### 3. POSTMAN
`route` 文件夹下 `Api_Lumen2.postman_collection.json` 有已经设置好的接口配置，可以导入 `postman` 具体查看。

### 4. 路由权限控制的逻辑
以 `/users`为例，请求从通过规定的路由发出，通过三个中间件 `auth` `api.permission` `api.timeslimit` ，分别代表 `token` 的鉴定控制，api 访问权限的控制，用户剩余可调用次数的控制，全部通过后，连接到 `UserController` 控制器，控制器从数据库调用数据进行包装和响应的构建，响应构建后，`api.timescounter` 作为后置中间件，记录 api 的调用日志。类似以下图这样的结构。

![类似这样一个结构](http://osv9x79o9.bkt.clouddn.com/18-1-23/51826687.jpg)

---

# V1.1.0 (passport 统一登录系统)
以 `V1.0.0` 为基础实现了 **passport** 统一登录系统。写有三翼通行证登录、密码绑定和成绩查询几个示例接口。

## 一、如何开始

### 1. 按步骤执行 **V1.0** 版本中 **如何开始** 的前五个小点

### 2. 通过 seed 填充数据到数据库
运行以下这句，填充教务系统和信息门户验证码识别数据到数据库：

```
php artisan db:seed --class=IdcodeSeeder
```

在 **/storage/app** 目录下新建 `idcodeTemp` 文件夹，把 **storage/** 目录都设置对应的可写权限。

## 二、如何使用

### 1. 绑定通行证
**文件：** \app\Http\Controllers\Passport\BindController.php
**方法：** bindPassword()
**路由：**

```
POST：/password/bind

sid：2015551509
edupd：*******
portalpd: *******     // 更多密码可选
```

**处理逻辑：** 得到账号密码后会用模型 `PassportCore` 中对应的方法对检验密码的正确性，正确则加密保存、保存此次 session 、记录登录时间，最后返回绑定结果。

### 2. 登录
**文件：** \app\Http\Controllers\Passport\LoginController.php
**方法：** login()
**路由：**

```
GET：/login

sid：2015551509
password：*******
signemail：xxxx@gmail.com   // 开发者或系统所持有的 key
signpassword：secret
type：sid   // 登录方式，sid 表示教务或信息门户都可以，此外还有 tel、qq、weixin 等
```

**处理逻辑：** 得到账号密码后会用模型 `PassportCore` 中对应的方法对检验密码的正确性，正确则加密保存、保存此次 session 、记录登录时间，最后返回一个  **token**，其中含有 **sid** 信息以及 **bindStatus** 信息，同时 **token** 还充当着权限判断的作用。
**bindStatus：**各个系统的绑定状态,1表示绑定,0表示没有绑定,用#分隔，顺序：一卡通、教务、图书馆、信息门户、报修、qq（以后可以自行补充，但不要修改顺序），例如： **0#1#0#1#0#0#0#**

### 3. 成绩查询
**文件：** \app\Http\Controllers\Passport\EduGradeController.php
**方法：** getGrade()
**路由：**

```
// term为0时，表示最近的学期，1-8是表示选择的学期（未读过的学期返回404），all表示所有成绩，其他返回参数错误
GET：/edu/grade/{term}  

token：XXXX.YYYY.ZZZZ
```

**处理逻辑：** **token** 分别经过中间件的有效判断、权限判断、可调用次数判断后会传入，然后解析得到 **sid** ，用 **sid** 首先看数据库中有没有 可用的 **sessionid**，有则直接利用然后爬取成绩，没有则直接获取加密密码解密，然后爬取数据。

## 三、其他
### 1. 验证码识别的实现
**文件：**\app\Models\Idcode.php


# 后续版本Todo

## 负责人
谢美玲、冯化昱、何佩佩

## 需求

### 1. api控制系统 ———— api_lumen （对应表）
- 完善 api 控制系统：用户注册

### 2. 三翼通行证 ———— passport （对应表）
- `PasswordController` 基础上完善更多系统的密码绑定，数据返回格式样例中已经比较详细
- 实现教务和信息门户其中任意一个密码就可以登录通行证（已完成）

### 3. 教务系统 & 信息门户 & 一卡通 & 图书馆 & 报修
- 完善以上系统所有接口，系统功能重合部分要实现代码复用。

---
以上为寒假必须完成的任务

以下为任务补充，有时间再完成
---
### 5. 学工系统常用接口：请假等

### 4. 基于学工系统的数据收集
- 每新增一个信息门户用户，用监听获取数据，完善 `passport-user-infos` 表，`\api\app\Events` 中已经创建监听文件。数据来源主要是学工系统，特别详细的数据比如**床位**什么的，你们看情况保存。


## 要求

- 使用 RESTful 风格（这个具体看我博客的那篇文章，针对三翼业务进行了修改）；
- 内部接口调用，尽量直接调用代码或使用内部调用，不用再去请求做好的接口；
- 密码要加密保存；
- 信息门户和其他系统的重合部分要实现代码复用；
- 命名按照建设好的命名风格进行；
- 代码格式：可借助 php-cs-fixer 格式化插件；
- 尝试用 git 协同开发，最终代码提交到 dev 分支，开学一起看过后合并到主分支

## 可能用到的知识点

- Eloquent 模型监听
- 内部调用

## 参考资料
- [Laravel 文档](https://d.laravel-china.org/)
- [Lumen 文档](https://lumen.laravel-china.org/)
- [速查表1](https://cs.laravel-china.org/)
- [速查表2](http://cheats.jesse-obrien.ca/)
- [Laravel 学院](http://laravelacademy.org/)
- [慕课-Laravel 入门](https://www.imooc.com/learn/697)
- [我的博客](http://blog.yfree.cc)
- [codecasts](https://www.codecasts.com/)







