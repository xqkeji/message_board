<?php
namespace core;

class Session {
    protected $prefix; // 会话前缀

    public function __construct() {
		$container=Container::getInstance();
		$config=$container->get('config');
        $appConfig = $config->get('app');
        $this->prefix = $appConfig['session_prefix']; // 从配置文件读取前缀
        
        // 启动会话（确保只启动一次）
        if (!session_id()) {
            session_start();
        }
    }

    // 设置会话变量
    public function set($key, $value) {
        $_SESSION[$this->prefix . $key] = $value;
    }

    // 获取会话变量
    public function get($key) {
        return isset($_SESSION[$this->prefix . $key]) ? $_SESSION[$this->prefix . $key] : null;
    }

    // 删除会话变量
    public function delete($key) {
        if (isset($_SESSION[$this->prefix . $key])) {
            unset($_SESSION[$this->prefix . $key]);
        }
    }

    // 清空所有会话
    public function clear(bool $destroy=true) {
        $_SESSION = [];
		if($destroy)
		{
			session_destroy();
		}
        
    }
}