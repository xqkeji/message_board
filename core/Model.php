<?php
namespace core;
use PDO;
use PDOException;
class Model {
    protected $pdo;       // PDO实例
    protected $table;     // 对应数据表名（子类需定义）
    // 构造函数：初始化数据库连接
    public function __construct() {
		$container=Container::getInstance();
		$config=$container->get('config');
        $dbConfig = $config->get('db');
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 开启错误抛出
        } catch (PDOException $e) {
            die("<div class='alert alert-danger text-center mt-5'>数据库连接失败：" . $e->getMessage() . "</div>");
        }
    }

    // 通用查询：执行SQL语句，返回PDOStatement对象
    public function query($sql, ?array $params = []) {
		try {
			$stmt = $this->pdo->prepare($sql);
			if(!empty($params))
			{
				// 遍历参数，自动处理LIMIT相关参数
				foreach ($params as $key => $value) {
					if(is_int($value))
					{
						$stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
					}
					else
					{
						// 其他参数按默认类型绑定
						$stmt->bindValue($key, $value);
					}
				}
			}
			$stmt->execute();
			return $stmt;
		} catch (PDOException $e) {
			die('SQL错误：' . $e->getMessage());
		}
	}

    // 获取单条数据：返回关联数组
    public function find($sql, ?array $params = []) {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    // 获取多条数据：返回二维关联数组
    public function select($sql, ?array $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 插入数据：接收关联数组，返回受影响行数
    public function insert($data) {
        $keys = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$keys}) VALUES ({$placeholders})";
        return $this->query($sql, $data)->rowCount();
    }

    // 更新数据：接收数据数组和条件数组，返回受影响行数
    public function update($data, $where) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key}=:{$key}";
        }
        $setStr = implode(',', $set);

        $whereStr = [];
        $whereParams = [];
        foreach ($where as $k => $v) {
            $whereStr[] = "{$k}=:where_{$k}";
            $whereParams[":where_{$k}"] = $v;
        }
        $whereStr = implode(' AND ', $whereStr);

        $sql = "UPDATE {$this->table} SET {$setStr} WHERE {$whereStr}";
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    // 删除数据：接收条件数组，返回受影响行数
    public function delete($where) {
        $whereStr = [];
        $params = [];
        foreach ($where as $k => $v) {
            $whereStr[] = "{$k}=:{$k}";
            $params[":{$k}"] = $v;
        }
        $whereStr = implode(' AND ', $whereStr);
        $sql = "DELETE FROM {$this->table} WHERE {$whereStr}";
        return $this->query($sql, $params)->rowCount();
    }

    // 获取最后插入的ID
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}