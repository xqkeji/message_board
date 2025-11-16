# 留言板message_board

## 一、项目简介

基于 PHP 8.0 + 与 MySQL 构建的响应式留言板，采用 MVC 设计模式，支持用户认证、留言管理、附件上传，适配 PC / 移动端，集成 SQL 注入防护、XSS 拦截等安全机制，兼顾学习与实用场景。

## 二、环境要求

PHP：8.0+（开启 PDO_MYSQL、GD 扩展）
MySQL：5.7+/MariaDB 10.0+
服务器：Apache 2.4+/Nginx 1.16+
浏览器：Chrome/Firefox/Edge（支持响应式）

## 三、部署步骤

下载项目：将源码解压至服务器目录，设置网站根目录为public或直接将message_board放入默认网站。
配置数据库：
新建数据库message_board（字符集utf8mb4_general_ci），执行db.sql建表；
修改config/db.php，填写 MySQL 主机、账号、密码。
权限设置：给public/upload目录赋予读写权限（Windows：Everyone 完全控制；Linux：chmod 777 public/upload）。
访问项目：浏览器输入地址（如http://localhost或如直接将message_board放入默认网站http://localhost/message_board），默认进入留言列表页。

## 四、核心功能

用户认证：注册（用户名 / 邮箱验重）、登录（验证码 + 记住登录）、密码修改；
留言管理：发布（≤500 字 +≤2MB 附件）、列表（分页 + 搜索）、修改 / 删除（仅作者操作）；
安全防护：PDO 预处理防注入、htmlspecialchars防 XSS、密码password_hash加密。

## 五、注意事项

验证码不显示：需确保resource/font/arial.ttf存在（可从 Windows 字体目录复制）；
附件上传失败：检查 php.ini 中upload_max_filesize≥2MB；
URL 重写：开启需配置 Apache/Nginx 重写规则，修改config/app.php中url_rewrite=true。