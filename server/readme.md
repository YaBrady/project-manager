## 项目概述
* 产品名称：通用小程序前台代码

## 运行环境要求

- Nginx 1.12+
- PHP 7.1+
- Mysql 5.6+
- Redis 3.0+

## 开发环境部署/安装

本项目代码使用 PHP 框架 [Laravel 5.5](https://d.laravel-china.org/docs/5.5/) 开发，本地开发环境使用 [Laravel Homestead](https://d.laravel-china.org/docs/5.5/homestead)。

下文将在假定已经安装好了 Homestead 的情况下进行说明。如果您还未安装 Homestead，可以参照 [Homestead 安装与设置](https://laravel-china.org/docs/5.5/homestead#installation-and-setup) 进行安装配置。

### 基础安装

#### 1. 克隆源代码

克隆 `wei+` 源代码到本地：

    > git clone -b http://wx.xuzhihong.top:8808/root/MiniProgram-Laravel.git

#### 2. 安装扩展包依赖

	composer install

#### 3. 生成配置文件

```
cp .env.example .env
```

你可以根据情况修改 `.env` 文件里的内容，如数据库连接、缓存、邮件设置等：
```
APP_URL=http://localhost

# dingo config
API_STANDARDS_TREE=prs
API_SUBTYPE=laravel
API_PREFIX=api
API_VERSION=v1
API_DEBUG=false

# jwt config
JWT_SECRET=
JWT_TTL=1440

# 小程序
WECHAT_MINI_PROGRAM_APPID=
WECHAT_MINI_PROGRAM_SECRET=
```

#### 4. 生成数据表及生成测试数据

在 Homestead 的网站根目录下运行以下命令

```shell
$ php artisan migrate --seed
```

#### 5. 生成秘钥

```shell
php artisan key:generate
php artisan jwt:secret 
```

#### 6. 生成秘钥

```
修改 .enn 找到 # 小程序
WECHAT_MINI_PROGRAM_APPID=
WECHAT_MINI_PROGRAM_SECRET=
将他填上
```

#### 7. 注意
```
APP_ENV 参数 可设置的内容，用于区分  在正式环境 必须使用 production！！！

开发: local

测试: testing

预上线: staging

正式环境: production
```