<?php
// 应用全局配置
return [
    'app_name' => 'MVC留言板',
    'upload_path' => ROOT_PATH. '/public/upload/', // 附件存储路径
    'upload_url' => 'upload/', // 附件访问URL（需根据项目路径调整）
    'session_prefix' => 'xq_sess_',                // 会话前缀（避免冲突）
	'remember_expire' => 3600 * 24 * 7,            // 记住登录有效期（7天）
	'page_size' => 10,                           // 分页默认条数（可在控制器中使用）
	'url_rewrite'=>false,                          //是否开启url重写
];