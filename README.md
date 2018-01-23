`Api-Lumen-Framework` 是一个用 `Lumen` 框架构建的**底层api基础框架**，你可以基于此框架快速构建你的api。

# V1.0.0
基本框架。

## 用到的框架及其版本
- Lumen v5.5
- dingo v2.0.0-alpha1
- JWT v1.0.0-rc.1

## 功能
- JWT 权限认证
- 灵活的权限控制
- api 调用统计
- api 调用速率限制

## 环境要求
- composer
- git

## 如何开始
### 1. 下载框架到本地

### 2. 执行 `composer update`

### 3. 在根目录新建你的 `.env` 文件
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

### 4. 运行数据库迁移 `composer dump-autoload` `php artisan migrate`
运行数据库迁移之前，先在数据库建立 `api_lumen` 和 `passport` 两个数据库。

### 5. 填充几条测试数据 `php artisan make:seeder`
这个命令会在 `users` 表中填充几条测试数据

### 6. 简单调用示例
权限分配的 E-R 图如下：

![ E-R 图](http://osv9x79o9.bkt.clouddn.com/18-1-16/68028673.jpg)

#### 6.1 权限分配路由暂未设置认证保护，你可以后面自己加入保护中

#### 6.2  先把要访问 `api` 加入 `api_items` 表中，注册 `api`
```
POST：/perm/item

key：USRE-GetAllUser
intro：获去所有的用户
url：http://127.0.0.1:8003/api/users
method：GET
```

#### 6.3 创建一个 `collection`
```
POST：/perm/collection

key：Collection-1
intro：第一个测试集合
istemp：boolean （可选，默认true）
```

#### 6.4 分配刚刚注册的 `api` 到 `collection`
```
POST：perm/contact

type：ci             // 可选 ci、cu、cg、gi，分别表示建立 collection 和 item，collection 和 user(email)， colletion 和 group，group 和 item 的联系
container：Collection-1（collection_key）
element：["USRE-GetAllUser"] （json数组）
```

#### 6.5 把 `collection` 分配给 `user`
```
POST: perm/contact

type：cu
container：Collection-1
element：["xxxx@gmail.com"] 
```

#### 6.6 设置用户的接口可调用次数
```
PATCH：perm/left-times

email：xxxx@gmail.com
times：1000
```

#### 6.7 获取 `token`
```
GET：auth/token

email：xxxx@gmail.com
password：secret （填充测试数据时，这里默认为secret）
```

#### 6.8 附带 `token` 访问数据
```
GET：api/users

token：eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDMvYXBpL2F1dGgvdG9rZW4iLCJpYXQiOjE1MTY0MzMyNjMsImV4cCI6MTUxNjQ0MDQ2MywibmJmIjoxNTE2NDMzMjYzLCJqdGkiOiJVcGFvRkxYQXRSY1lxRGRiIiwic3ViIjoxLCJwcnYiOiI4N2UwYWYxZWY5ZmQxNTgxMmZkZWM5NzE1M2ExNGUwYjA0NzU0NmFhIn0.1RAaVARi1qj1evvGxEiaCeP1Z-hDsBTRz2p1YLbC9GM
```

## 其他

### 1. `token` 的设置（有效时间，刷新时间，宽限时间）
`config/jwt.php`

### 2. 客户端如何维护 `token`
`token` 的第二段 `base64` 解码可得到有效时间，在过期时 `PATCH` 请求 `/auth/token` 更新 `token`。

### 3. POSTMAN
`route` 文件夹下 `Api_Lumen2.postman_collection.json` 是用 `postman` 导出的功能接口，可以导入 `postman` 具体查看


# V1.1.0
以 `V1.0.0` 为基础的三翼业务接口。写有三翼通行证密码绑定和成绩查询两个示例接口。

## 新增

### 1. 教务系统和信息门户的验证码数据，可通过 `seeder` 自动填充到数据库
运行以下这句，填充验证码识别数据到数据库
```
php artisan db:seed --class=IdcodeSeeder
```

### 2. 模型

#### 2.1 验证码识别模型
**\app\Models\Idcode.php**

方法 `EduIdcode` 实现了教务验证码识别。

#### 2.2 登录核心
**\app\Models\PassportCore.php**

方法 `eduLogin` 实现了教务登录，其中用 `sessionid` 实现了二次免登录。

### 3. 控制器

#### 3.1 通行证绑定
**\app\Http\Controllers\Passport\PasswordController.php**

方法 `bindPassword` 实现了各系统的密码绑定，现只有教务。

路由：
```
GET：password/bind

sid：2015551509
edupd：*******
......     // 更多密码可选
```

#### 3.2. 成绩查询
**\app\Http\Controllers\Passport\EduGradeController.php**

方法 `getGrade` 实现了成绩查询。

路由：
```
GET：edu/grade/{term}        // term为0时，表示最近的学期，1-8是表示选择的学期（未读过的学期返回404），all表示所有成绩，其他返回参数错误

sid：2015551509
edupd：*******
sessionid：上一次的sessionid（可选）
```


# 后续版本Todo

## 负责人
谢美玲、冯化昱

## 需求

### 1. api控制系统 ———— api_lumen （对应表）
- 完善 api 控制系统：用户注册

### 2. 三翼通行证 ———— passport （对应表）
- `PasswordController` 基础上完善更多系统的密码绑定，数据返回格式样例中已经比较详细

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
- 两人尝试用 git 协同开发，最终代码提交到 dev 分支，开学一起看过后合并到主分支

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


