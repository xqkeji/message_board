<?php
use core\Config;
use core\Container;
use util\Url;

// 定义项目根路径常量
define('ROOT_PATH', dirname(__DIR__));
define('DS',DIRECTORY_SEPARATOR);
// 自动加载：实现类的自动引入（无需手动require）
spl_autoload_register(function($className) {
    $classPath = str_replace(DS, '/', $className) . '.php';
	$filename=ROOT_PATH.DS.$classPath;
    
	if (file_exists($filename)) {
		require $filename;
		return;
	}
    
});
// 初始化容器
$container = Container::getInstance();
//绑定配置服务（闭包绑定，延迟实例化）
$configDir = ROOT_PATH . DS.'config'.DS;
$config=new Config($configDir);
$container->bind('config', $config);

$url=new Url('',$config->get('app.url_rewrite'));
$container->bind('url', $url);

// 解析请求参数：从 _url 中拆分控制器和动作
$controller = 'message'; // 默认控制器
$action = 'index';       // 默认动作

if (isset($_GET['_url'])) {
	
    $baseUrl=ltrim($url->getBaseUrl(),'/');
	$urlPath = trim($_GET['_url'], '/'); // 去除首尾斜杠，如 "Message/index"
	$urlPath=str_replace($baseUrl,'',$urlPath);
    $pathParts = explode('/', $urlPath); // 拆分为数组：["Message", "index"]
    // 验证拆分结果，至少包含控制器（动作可选）
    if (!empty($pathParts[0])) {
        $controller = $pathParts[0]; // 第一个部分为控制器
        // 第二个部分为动作（若存在）
        if (!empty($pathParts[1])) {
            $action = $pathParts[1];
        }
    }
    
    // 移除 _url 参数，避免参数注入时混淆
    unset($_GET['_url']);
}

// 控制器类名规范：控制器名+Controller（首字母大写，支持下划线/连字符转驼峰）
$controllerName = $url->camelCase($controller) . 'Controller';
$controllerClass = "controller\\{$controllerName}";
$action=$url->camelCase($action,false);
// 验证控制器和动作是否存在
if (!class_exists($controllerClass)) {
    die("控制器不存在：{$controllerClass}");
}
if (!method_exists($controllerClass, $action)) {
    die("动作不存在：{$controllerClass}->{$action}()");
}

// 实例化控制器
$controllerObj = new $controllerClass();
$container->bind('controller', $controllerObj);
// 将当前请求参数传递给视图（用于导航高亮）
$controllerObj->view->assign('current_c', strtolower($controller));
$controllerObj->view->assign('current_a', strtolower($action));

//执行动作
$callParams = $controllerObj->buildActionParams($action);
call_user_func_array([$controllerObj, $action], $callParams);
