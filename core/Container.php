<?php
namespace core;
class Container
{
    // 存储绑定的依赖（键为服务名，值为实例或闭包）
    private array $bindings = [];

    // 存储已实例化的对象（缓存，避免重复实例化）
    private array $instances = [];

    // 容器单例实例
    private static ?Container $instance = null;

    /**
     * 私有构造函数：防止外部直接实例化（单例模式）
     */
    private function __construct() {}

    /**
     * 获取容器单例实例
     * @return Container 容器实例
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 绑定依赖到容器
     * @param string $name 服务名称（唯一标识）
     * @param mixed $concrete 绑定内容（实例或闭包）
     */
    public function bind(string $name, mixed $concrete): void
    {
        $this->bindings[$name] = $concrete;
        // 绑定新依赖时，清除对应实例缓存（如需更新实例）
        unset($this->instances[$name]);
    }

    /**
     * 从容器获取已实例化的对象（核心新增方法）
     * - 优先从实例缓存中获取，避免重复实例化
     * - 缓存中没有则自动解析并缓存
     * @param string $name 服务名称
     * @param array $params 类实例化时的构造参数（仅首次实例化有效）
     * @return mixed 已实例化的对象
     * @throws \Exception 服务未绑定且无法实例化时抛出异常
     */
    public function get(string $name, array $params = []): mixed
    {
        // 1. 优先从实例缓存获取（已实例化过直接返回）
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // 2. 缓存中没有，解析实例并缓存
        $instance = $this->make($name, $params);
        $this->instances[$name] = $instance;

        return $instance;
    }

    /**
     * 从容器解析依赖（内部使用，支持自动实例化无参类）
     * @param string $name 服务名称
     * @param array $params 类实例化时的构造参数（可选）
     * @return mixed 解析后的实例
     * @throws \Exception 服务未绑定且无法实例化时抛出异常
     */
    public function make(string $name, array $params = []): mixed
    {
        // 1. 优先使用已绑定的依赖
        if (isset($this->bindings[$name])) {
            $concrete = $this->bindings[$name];
            // 闭包绑定：执行闭包生成实例
            if ($concrete instanceof \Closure) {
                return $concrete($this, $params);
            }
            // 实例绑定：直接返回已存在的实例
            return $concrete;
        }

        // 2. 未绑定服务时，尝试直接实例化类
        if (!class_exists($name)) {
            throw new \Exception("服务未绑定且类不存在：{$name}");
        }

        // 反射机制：处理类的构造函数参数（支持依赖注入）
        $reflector = new \ReflectionClass($name);
        $constructor = $reflector->getConstructor();

        // 无构造函数时直接实例化
        if ($constructor === null) {
            return new $name();
        }

        // 解析构造函数参数（优先使用传入的params，否则从容器获取）
        $parameters = $constructor->getParameters();
        $resolvedParams = [];

        foreach ($parameters as $param) {
            $paramType = $param->getType();
            // 有类型提示的参数：从容器获取（递归解析依赖）
            if ($paramType !== null && !$paramType->isBuiltin()) {
                $resolvedParams[] = $this->get((string)$paramType);
            }
            // 无类型提示的参数：从传入的params中获取（需按顺序）
            else {
                $resolvedParams[] = array_shift($params) ?? $param->getDefaultValue();
            }
        }

        // 实例化类并传入解析后的参数
        return $reflector->newInstanceArgs($resolvedParams);
    }

    /**
     * 快速绑定并获取实例（绑定+get的快捷方法）
     * @param string $name 服务名称
     * @param mixed $concrete 绑定内容（实例或闭包）
     * @return mixed 已实例化的对象
     */
    public function instance(string $name, mixed $concrete): mixed
    {
        $this->bind($name, $concrete);
        return $this->get($name);
    }

    /**
     * 手动清除实例缓存（可选）
     * @param string|null $name 服务名称（为空则清除所有缓存）
     */
    public function clearInstance(?string $name = null): void
    {
        if ($name === null) {
            $this->instances = [];
        } else {
            unset($this->instances[$name]);
        }
    }
}