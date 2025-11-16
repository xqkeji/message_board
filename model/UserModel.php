<?php
namespace model;
use core\Model;
class UserModel extends Model {
    protected $table = 'user'; // 对应数据表名

    // 注册用户：接收用户名、邮箱、密码，返回受影响行数
    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); // 密码加密
        return $this->insert($data);
    }

    // 登录验证：接收用户名和密码，返回用户信息或false
    public function login($username, $password) {
        $user = $this->find("SELECT * FROM {$this->table} WHERE username = :username", [':username' => $username]);
        if ($user && password_verify($password, $user['password'])) {
            return $user; // 验证成功，返回用户信息
        }
        return false; // 验证失败
    }
	// 根据remember_token获取用户
    public function getUserById(int $id) {
        return $this->find("SELECT * FROM {$this->table} WHERE id = :id", [':id' => $id]);
    }
    // 根据remember_token获取用户
    public function getUserByRememberToken($token) {
        return $this->find("SELECT * FROM {$this->table} WHERE remember_token = :token", [':token' => $token]);
    }

    // 设置remember_token
    public function setRememberToken($userId, $token) {
        return $this->update(['remember_token' => $token], ['id' => $userId]);
    }

    // 修改密码：接收用户ID和新密码
    public function changePassword($userId, $newPassword) {
        $password = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update(['password' => $password], ['id' => $userId]);
    }

    // 检查用户名是否已存在
    public function checkUsernameExists($username) {
        $user = $this->find("SELECT id FROM {$this->table} WHERE username = :username", [':username' => $username]);
        return !empty($user);
    }

    // 检查邮箱是否已存在
    public function checkEmailExists($email) {
        $user = $this->find("SELECT id FROM {$this->table} WHERE email = :email", [':email' => $email]);
        return !empty($user);
    }
}