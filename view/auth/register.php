<?php $this->title = '用户注册'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card p-4 p-md-5">
            <h2 class="card-title text-center mb-4">
                <i class="fa fa-user-plus text-primary me-2"></i>用户注册
            </h2>
            <form method="post" action="<?=$this->url->to('auth','register')?>">
                <div class="row mb-3">
                    <label for="username" class="col-sm-3 col-form-label">用户名</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="username" name="username" required
                               placeholder="3-20位字母数字下划线">
                        <div class="form-text">支持字母、数字和下划线，3-20个字符</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="email" class="col-sm-3 col-form-label">邮箱</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="email" required
                               placeholder="example@domain.com">
                        <div class="form-text">用于账号验证和密码找回</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="password" class="col-sm-3 col-form-label">密码</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="至少6位字符">
                        <div class="form-text">密码长度不能少于6位</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label for="confirm_password" class="col-sm-3 col-form-label">确认密码</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                               placeholder="再次输入密码">
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?=$this->url->to('auth','login')?>" class="btn btn-outline-secondary me-md-2">已有账号？登录</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check me-1"></i>注册
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>