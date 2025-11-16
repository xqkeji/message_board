<?php
namespace util;
class Validate {
    // 验证必填字段：所有字段不能为空
    public static function required($fields) {
        foreach ($fields as $field) {
            if (empty(trim($field))) {
                return false;
            }
        }
        return true;
    }

    // 验证用户名：3-20位字母、数字、下划线
    public static function username($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }

    // 验证邮箱格式
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // 验证密码长度：至少6位
    public static function passwordLength($password) {
        return strlen($password) >= 6;
    }
}