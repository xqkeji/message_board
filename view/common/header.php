<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->appConfig['app_name']; ?> - <?php echo isset($title) ? $title : '首页'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 图标 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <!-- 自定义微调样式 -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .alert {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php?c=Message&a=index">
                <i class="fa fa-comments-o me-2"></i>MVC留言板
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_c === 'message' && $current_a === 'index') ? 'active' : ''; ?>" href="<?=$this->url->to('message','index')?>">
                            <i class="fa fa-list-alt me-1"></i>留言列表
                        </a>
                    </li>
                    <?php if ($this->session->get('user_id')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_c === 'message' && $current_a === 'create') ? 'active' : ''; ?>" href="<?=$this->url->to('message','create')?>">
                                <i class="fa fa-pencil me-1"></i>发布留言
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($this->session->get('user_id')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa fa-user me-1"></i><?php echo $this->session->get('username'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item <?php echo ($current_c === 'auth' && $current_a === 'changepassword') ? 'active' : ''; ?>" href="<?=$this->url->to('auth','change-password')?>">
                                        <i class="fa fa-key me-1"></i>修改密码
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?=$this->url->to('auth','logout')?>">
                                        <i class="fa fa-sign-out me-1"></i>退出登录
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_c === 'auth' && $current_a === 'login') ? 'active' : ''; ?>" href="<?=$this->url->to('auth','login')?>">
                                <i class="fa fa-sign-in me-1"></i>登录
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_c === 'auth' && $current_a === 'register') ? 'active' : ''; ?>" href="<?=$this->url->to('auth','register')?>">
                                <i class="fa fa-user-plus me-1"></i>注册
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主内容区 -->
    <main class="container py-5">
        <?php include __DIR__ . '/alert.php'; ?>