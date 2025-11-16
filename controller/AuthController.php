<?php
namespace controller;

use core\Controller;
use core\Captcha;
use model\UserModel;
use util\Validate;

class AuthController extends Controller {
    // 注册页面：GET请求显示页面，POST请求处理注册
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 接收表单数据
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);

            // 数据验证
            if (!Validate::required([$username, $email, $password, $confirmPassword])) {
                $this->redirect('Auth', 'register', '所有字段不能为空！', 'error');
            }
            if (!Validate::username($username)) {
                $this->redirect('Auth', 'register', '用户名格式错误！支持3-20位字母、数字、下划线', 'error');
            }
            if (!Validate::email($email)) {
                $this->redirect('Auth', 'register', '邮箱格式错误！', 'error');
            }
            if ($password !== $confirmPassword) {
                $this->redirect('Auth', 'register', '两次密码输入不一致！', 'error');
            }
            if (!Validate::passwordLength($password)) {
                $this->redirect('Auth', 'register', '密码长度不能少于6位！', 'error');
            }

            // 检查用户名和邮箱是否已存在
            $userModel = new UserModel();
            if ($userModel->checkUsernameExists($username)) {
                $this->redirect('Auth', 'register', '用户名已被注册！', 'error');
            }
            if ($userModel->checkEmailExists($email)) {
                $this->redirect('Auth', 'register', '邮箱已被注册！', 'error');
            }

            // 注册用户
            $result = $userModel->register([
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);

            if ($result) {
                $this->redirect('Auth', 'login', '注册成功！请登录', 'success');
            } else {
                $this->redirect('Auth', 'register', '注册失败，请重试！', 'error');
            }
        }

        // GET请求：渲染注册页面
        $this->view->render('auth/register');
    }

    // 登录页面：GET请求显示页面，POST请求处理登录
    public function login() {
        // 已登录用户直接跳转至留言列表
        if ($this->session->get('user_id')) {
            $this->redirect('Message', 'index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 接收表单数据
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $captcha = trim($_POST['captcha']);
            $remember = isset($_POST['remember']) ? 1 : 0;
			
            // 数据验证
            if (!Validate::required([$username, $password, $captcha])) {
                $this->redirect('Auth', 'login', '所有字段不能为空！', 'error');
            }
            if (!Captcha::check($captcha)) {
                $this->redirect('Auth', 'login', '验证码错误！', 'error');
            }

            // 登录验证
            $userModel = new UserModel();
            $user = $userModel->login($username, $password);

            if ($user) {
                // 存储用户信息到会话
                $this->session->set('user_id', $user['id']);
                $this->session->set('username', $user['username']);

                // 记住登录：生成token并存储到数据库和Cookie
                if ($remember) {
                    $token = md5(uniqid() . $user['id'] . time() . mt_rand(1000, 9999));
                    $userModel->setRememberToken($user['id'], $token);
                    setcookie(
                        'remember_token', 
                        $token, 
                        time() + $this->appConfig['remember_expire'], 
                        '/', 
                        '', 
                        false, 
                        true // Cookie仅HTTP可用，防止JS读取
                    );
                }

                // 登录成功跳转至留言列表（支持从其他页面跳转过来的参数）
                $params = [];
                if (!empty($_GET['page'])) $params['page'] = $_GET['page'];
                if (!empty($_GET['keyword'])) $params['keyword'] = $_GET['keyword'];
                $this->redirect('Message', 'index', '登录成功！', 'success', $params);
            } else {
                $this->redirect('Auth', 'login', '用户名或密码错误！', 'error');
            }
        }

        // GET请求：渲染登录页面
        $this->view->render('auth/login');
    }

    // 退出登录
    public function logout() {
        // 清除会话
        $this->session->clear();
        // 清除remember_token Cookie
        setcookie('remember_token', '', time() - 3600, '/');
        $this->redirect('Auth', 'login', '退出登录成功！', 'success');
    }

    // 修改密码页面
    public function changePassword() {
        $this->checkLogin(); // 必须登录才能访问

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 接收表单数据
            $oldPassword = trim($_POST['old_password']);
            $newPassword = trim($_POST['new_password']);
            $confirmPassword = trim($_POST['confirm_password']);

            // 数据验证
            if (!Validate::required([$oldPassword, $newPassword, $confirmPassword])) {
                $this->redirect('Auth', 'changePassword', '所有字段不能为空！', 'error');
            }
            if ($newPassword !== $confirmPassword) {
                $this->redirect('Auth', 'changePassword', '两次新密码输入不一致！', 'error');
            }
            if (!Validate::passwordLength($newPassword)) {
                $this->redirect('Auth', 'changePassword', '新密码长度不能少于6位！', 'error');
            }

            // 验证原密码
            $userModel = new UserModel();
            $userId = $this->session->get('user_id');
            $user = $userModel->getUserById($userId);
            if (!$user || !password_verify($oldPassword, $user['password'])) {
                $this->redirect('Auth', 'changePassword', '原密码错误！', 'error');
            }

            // 修改密码
            $result = $userModel->changePassword($userId, $newPassword);
            if ($result) {
				// 清除会话
				$this->session->clear(false);
				// 清除remember_token Cookie
				setcookie('remember_token', '', time() - 3600, '/');
                $this->redirect('Auth', 'login', '密码修改成功！请重新登录', 'success');
            } else {
                $this->redirect('Auth', 'changePassword', '密码修改失败，请重试！', 'error');
            }
        }

        // GET请求：渲染修改密码页面
        $this->view->render('auth/change_password');
    }

    // 生成验证码图片
    public function captcha() {
        $captcha = new Captcha();
        $captcha->generate();
    }
}