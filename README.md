`Api-Lumen-Framework` 是一个用 `Lumen` 框架构建的**底层api基础框架**，你可以基于此框架快速构建你的api。

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

### 4. 运行数据库迁移 `php artisan migrate`

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