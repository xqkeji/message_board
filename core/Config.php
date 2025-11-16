<?php
namespace core;
class Config
{
    // 存储所有配置的数组
    private array $config = [];

    // 配置文件目录路径
    private string $configDir;

    /**
     * 构造函数：初始化配置目录并加载所有配置
     * @param string $configDir 配置文件所在目录（绝对路径）
     * @throws \Exception 当配置目录不存在时抛出异常
     */
    public function __construct(string $configDir)
    {
        $this->configDir = rtrim($configDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        // 验证配置目录有效性
        if (!is_dir($this->configDir)) {
            throw new \Exception("配置目录不存在：{$this->configDir}");
        }

        // 加载目录下所有.php配置文件
        $this->loadAllConfig();
    }

    /**
     * 加载配置目录下所有PHP格式配置文件
     */
    private function loadAllConfig(): void
    {
        // 匹配所有.php后缀的配置文件
        $configFiles = glob($this->configDir . '*.php');
        
        foreach ($configFiles as $file) {
            // 排除目录中的非文件项
            if (!is_file($file)) continue;

            // 读取配置文件（文件需返回数组格式）
            $fileConfig = include $file;
            if (!is_array($fileConfig)) continue;

            // 以文件名作为配置键（如db.php对应config['db']）
            $configKey = pathinfo($file, PATHINFO_FILENAME);
            $this->config[$configKey] = array_merge_recursive(
                $this->config[$configKey] ?? [],
                $fileConfig
            );
        }
    }

    /**
     * 获取配置项（支持点语法，如"db.host"）
     * @param string $key 配置键（空值返回所有配置）
     * @param mixed $default 配置不存在时的默认值
     * @return mixed 配置值或默认值
     */
    public function get(string $key = '', mixed $default = null): mixed
    {
        // 无键时返回所有配置
        if (empty($key)) return $this->config;

        $currentConfig = $this->config;
        // 分割点语法键（如"db.host"分割为["db", "host"]）
        $keyParts = explode('.', $key);

        foreach ($keyParts as $part) {
            // 键不存在时返回默认值
            if (!isset($currentConfig[$part])) return $default;
            $currentConfig = $currentConfig[$part];
        }

        return $currentConfig;
    }

    /**
     * 手动设置配置项
     * @param string $key 配置键（支持点语法）
     * @param mixed $value 配置值
     */
    public function set(string $key, mixed $value): void
    {
        $keyParts = explode('.', $key);
        $configRef = &$this->config;

        foreach ($keyParts as $index => $part) {
            // 最后一个键直接赋值，其他键创建空数组
            if ($index === count($keyParts) - 1) {
                $configRef[$part] = $value;
                break;
            }
            // 键不存在时初始化空数组
            if (!isset($configRef[$part])) $configRef[$part] = [];
            $configRef = &$configRef[$part];
        }

        // 释放引用避免内存问题
        unset($configRef);
    }
}