<?php
namespace core;
use util\Url;
class View {
    protected $data = []; // 存储传递给视图的变量
	public $session;      // 会话实例
	public $appConfig;    // 应用配置
	public $url;
	public function __construct() {  
		$container=Container::getInstance();
		$config=$container->get('config');
        $this->session = $container->get('session');    // 获取会话实例
		$this->appConfig = $config->get('app'); // 加载应用配置
		$this->url=$container->get('url');
    }
    // 分配变量到视图
    public function assign($key, $value) {
        $this->data[$key] = $value;
    }
	// 魔术方法：通过 $this->变量名 直接访问 data 中的数据
	public function __get($key) {
        // 当访问的属性存在于data数组中时返回对应值，否则返回null（避免报错）
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
	// 魔术方法：通过 $this->变量名 直接修改 data 中的数据
	public function __set($key, $value) {
		$this->data[$key] = $value;
	}
    // 魔术方法：判断变量是否存在（可选，增强兼容性）
    public function __isset($key) {
        return isset($this->data[$key]);
    }
    // 渲染视图：加载公共头、尾和当前视图
    public function render($viewPath) {
        extract($this->data); // 提取变量，可在视图中直接使用
        
        // 加载公共头（含Bootstrap、导航栏）
        $headerFile = ROOT_PATH . '/view/common/header.php';
        if (file_exists($headerFile)) {
            include $headerFile;
        } else {
            die("视图文件缺失：{$headerFile}");
        }

        // 加载当前视图（如auth/login、message/index）
        $viewFile = ROOT_PATH . '/view/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            die("视图文件缺失：{$viewFile}");
        }

        // 加载公共尾（含JS、页脚）
        $footerFile = ROOT_PATH . '/view/common/footer.php';
        if (file_exists($footerFile)) {
            include $footerFile;
        } else {
            die("视图文件缺失：{$footerFile}");
        }
    }
	
}