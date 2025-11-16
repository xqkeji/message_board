<?php
namespace util;
use core\Container;
/**
 * URL生成工具类：生成 _url=控制器/动作 的URL格式
 */
class Url {
    private $baseUrl; // 项目基础URL（如：/message_board/public/）
	private $urlRewrite; //是否启动url重写
	private $appConfig;    // 应用配置
	private $container;		//容器对象
    public function __construct(string $baseUrl='',bool $urlRewrite=false)
    {
        if(empty($baseUrl))
		{
			$this->baseUrl=str_replace('index.php','',$_SERVER['PHP_SELF']);
		}
		else
		{
			$this->baseUrl=$baseUrl;
		}
		$this->urlRewrite=$urlRewrite;
		$container=Container::getInstance();
		$this->container=$container;
		$config=$container->get('config');
		$this->appConfig = $config->get('app'); // 加载应用配置
		
    }
	
	public function getBaseUrl():string
	{
		return $this->baseUrl;
	}
    // 4. 私有克隆方法：禁止外部通过 clone 复制实例
    private function __clone() {}

    // 5. 私有反序列化方法：禁止通过 unserialize 生成新实例
    public function __wakeup()
    {
        // 可选：抛异常明确禁止反序列化，增强安全性
        throw new \RuntimeException("禁止反序列化单例类 " . __CLASS__);
    }

    /**
     * 生成URL（核心方法：生成 _url=控制器/动作 格式）
     * @param string $controller 控制器名（无需加Controller后缀）
     * @param string $action 动作名
     * @param array $params 额外参数（键值对数组）
     * @return string 完整URL
     */
    public function to($controller, $action, $params = []) {
		$urlRewrite=$this->urlRewrite;
		$queryParams=[];
		if(!$urlRewrite)
		{	
			// 基础URL + 入口文件
			$url = $this->baseUrl . 'index.php';
	
			if(isset($params['_url']))
			{
				unset($params['_url']);
			}
			// 合并额外参数，过滤空值（null/空字符串/空数组）
			foreach ($params as $key => $value) {
				if (is_scalar($value) && $value !== '' && $value !== null) {
					$queryParams[$key] = $value;
				}
			}

			// 拼接查询字符串（自动URL编码）
			$queryString = http_build_query($queryParams);
			// 核心参数：_url=控制器/动作
			$url=$url . "?_url={$controller}/{$action}";
			if(empty($queryString))
			{
				return $url;
			}
			else
			{
				return $url.'&'.$queryString;
			}
		}
		else
		{
			// 基础URL + 入口文件
			$url = $this->baseUrl."{$controller}/{$action}";
			if(isset($params['_url']))
			{
				unset($params['_url']);
			}
			// 合并额外参数，过滤空值（null/空字符串/空数组）
			foreach ($params as $key => $value) {
				if (is_scalar($value) && $value !== '' && $value !== null) {
					$queryParams[$key] = $value;
				}
			}

			// 拼接查询字符串（自动URL编码）
			$queryString = http_build_query($queryParams);
			if(!empty($queryString))
			{
				return $url . '?' . $queryString;
			}
			else
			{
				return $url;
			}
		}
    }

    /**
     * 生成当前页面的URL（保留现有参数，可覆盖）
     * @param array $params 要覆盖的参数（如：['page' => 2]）
     * @return string 当前页面URL
     */
    public function current($params = []) {
		$view=$this->container->get('view');
		// 获取当前请求的控制器和动作（从视图传递的变量，或默认值）
		$controller=$view->current_c;
		$action=$view->current_a;
        
        $currentController = isset($controller) ? $controller : 'message';
        $currentAction = isset($action) ? $action : 'index';

        // 合并当前参数和覆盖参数（排除 _url，已通过控制器/动作生成）
        $currentParams = $_GET;
        $newParams = array_merge($currentParams, $params);

        // 生成当前页面URL
        return $this->to($currentController, $currentAction, $newParams);
    }
	 /**
     * 生成带page的URL（保留现有参数，可覆盖）
     * @param array $params 要覆盖的参数（如：['page' => 2]）
     * @return string 跳转URL
     */
    public function page(int $page,$params = []) {
        $view=$this->container->get('view');
		// 获取当前请求的控制器和动作（从视图传递的变量，或默认值）
		$controller=$view->current_c;
		$action=$view->current_a;
        $currentController = isset($controller) ? $controller : 'message';
        $currentAction = isset($action) ? $action : 'index';
		$_GET['page']=$page;
        // 合并当前参数和覆盖参数（排除 _url，已通过控制器/动作生成）
        $currentParams = $_GET;
        $newParams = array_merge($currentParams, $params);

        // 生成当前页面URL
        return $this->to($currentController, $currentAction, $newParams);
    }
	/**
     * 生成附件访问URL
     * @param string $filename 附件文件名
     * @return string 附件完整访问URL
     */
    public function upload($filename) {
		$appConfig=$this->appConfig;
		$baseUrl=$this->baseUrl;
        if (empty($filename)) {
            return '';
        }
        // 从配置读取附件基础URL，拼接文件名（自动处理斜杠）
        $uploadUrl = $appConfig['upload_url'];
        return $baseUrl.$uploadUrl . ltrim($filename, '/');
    }
	/**
	 * 字符串转驼峰法函数
	 * @param string $str 原始字符串（支持下划线、连字符、空格分隔）
	 * @param bool $ucfirst 是否首字母大写（true：大驼峰，false：小驼峰，默认true）
	 * @return string 驼峰格式字符串
	 */
	public function camelCase($str, $ucfirst = true) {
		// 1. 处理特殊情况：空字符串直接返回
		if (empty($str)) {
			return '';
		}

		// 2. 将下划线、连字符、空格统一替换为单一分隔符（下划线），并过滤连续分隔符
		$str = preg_replace('/[-_\s]+/', '_', $str);

		// 3. 移除字符串首尾的分隔符
		$str = trim($str, '_');

		// 4. 处理字符串：将分隔符后的字符转为大写，同时移除分隔符
		$str = preg_replace_callback('/_([a-zA-Z0-9])/', function($matches) {
			// 匹配到 "_x" 格式，将 x 转为大写并返回
			return strtoupper($matches[1]);
		}, $str);

		// 5. 处理首字母大小写
		if ($ucfirst) {
			$str = ucfirst($str); // 大驼峰（默认）
		} else {
			$str = lcfirst($str); // 小驼峰
		}

		// 6. 返回最终结果
		return $str;
	}

	/**
	 * 驼峰字符串转分隔符格式（支持下划线_或连字符-）
	 * @param string $str 驼峰格式字符串（大驼峰/小驼峰均可）
	 * @param string $separator 目标分隔符（支持 '_' 或 '-', 默认 '_'）
	 * @param bool $lowercase 是否全转为小写（默认true，false保留原有大小写）
	 * @return string 分隔符格式字符串
	 */
	public function unCamelCase($str, $separator = '_', $lowercase = true) {
		// 1. 处理特殊情况：空字符串直接返回
		if (empty($str)) {
			return '';
		}

		// 2. 校验分隔符合法性：仅支持 '_' 和 '-'
		if (!in_array($separator, ['_', '-'])) {
			trigger_error('分隔符仅支持 "_" 和 "-"', E_USER_WARNING);
			$separator = '_'; // 非法分隔符时默认使用下划线
		}

		// 3. 驼峰转分隔符：在大写字母前添加指定分隔符
		// 正则说明：匹配大写字母，排除字符串开头的大写字母（避免首字符前加分隔符）
		$str = preg_replace('/(?<!^)([A-Z])/', $separator . '$1', $str);

		// 4. 移除首尾分隔符（防止字符串开头是大写字母导致的首字符前分隔符）
		$str = trim($str, $separator);

		// 5. 过滤连续分隔符（极端情况处理）
		$str = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $str);

		// 6. 是否全转为小写（默认开启）
		if ($lowercase) {
			$str = strtolower($str);
		}

		// 7. 返回最终结果
		return $str;
	}
	
}