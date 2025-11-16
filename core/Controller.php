<?php
namespace core;
use util\Url;
class Controller {
    public $view;         // 视图实例
    protected $session;      // 会话实例
    protected $appConfig;    // 应用配置
    public function __construct() {
		$container=Container::getInstance();
		$config=$container->get('config');
		// 初始化会话,要比视图先初始化，视图里也用到会话
		$session=$container->instance('session', new Session());
        $this->session = $session;    
		// 初始化视图
        $view=$container->instance('view', new View());        
		$this->view=$view;
		
        $this->appConfig = $config->get('app'); // 加载应用配置
        $this->autoLogin(); // 自动登录（基于remember_token）
    }

    // 自动登录逻辑：未登录但有remember_token cookie时触发
    protected function autoLogin() {
        if (!$this->session->get('user_id') && isset($_COOKIE['remember_token'])) {
            $userModel = new \model\UserModel(); // 实例化用户模型
            $user = $userModel->getUserByRememberToken($_COOKIE['remember_token']);
            if ($user) {
                // 登录成功，设置会话
                $this->session->set('user_id', $user['id']);
                $this->session->set('username', $user['username']);
            }
        }
    }

    // 登录检查：未登录用户跳转至登录页
    protected function checkLogin() {
        if (!$this->session->get('user_id')) {
            $this->session->set('error', '请先登录后再操作！');
            $this->redirect(‘auth’,’login’);
        }
    }

    // 跳转函数：带提示信息（URL改为新格式）
    protected function redirect($c, $a, $message = '', $type = 'success', $params = []) {
        if ($message) {
            $this->session->set($type, $message); // 存储提示信息到会话
        }
        //构建跳转地址
		$url=$this->view->url->to($c,$a,$params);
        header("Location: {$url}");
        exit;
    }
	/**
	 * 组装控制器动作调用参数
	 * @param object $controller 控制器实例
	 * @param string $action 动作名
	 * @return array 组装后的调用参数
	 * @throws Exception 缺少必填参数时抛出异常
	 */
	public function buildActionParams($action) {
		$reflectionMethod = new \ReflectionMethod($this, $action);
		$methodParams = $reflectionMethod->getParameters();
		$callParams = [];
		
		foreach ($methodParams as $param) {
			$paramName = $param->getName();
			$paramType = $param->getType();
			
			if (isset($_GET[$paramName])) {
				$paramValue = $_GET[$paramName];
				// 类型转换逻辑...
				$callParams[] = $paramValue;
			} elseif ($param->isDefaultValueAvailable()) {
				$callParams[] = $param->getDefaultValue();
			} else {
				throw new Exception("缺少必填参数：{$paramName}");
			}
		}
		
		return $callParams;
	}
}