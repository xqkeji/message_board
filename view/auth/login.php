<?php $this->title = '用户登录'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="card p-4 p-md-5">
            <h2 class="card-title text-center mb-4">
                <i class="fa fa-sign-in text-primary me-2"></i>用户登录
            </h2>
            <form method="post" action="<?=$this->url->to('auth','login')?>">
                <div class="mb-3">
                    <label for="username" class="form-label">用户名</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-user"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" required
                               placeholder="请输入用户名">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">密码</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="请输入密码">
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-md-7">
                        <label for="captcha" class="form-label">验证码</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-shield"></i>
                            </span>
                            <input type="text" class="form-control" id="captcha" name="captcha" required
                                   placeholder="请输入验证码">
                        </div>
                    </div>
                    <div class="col-md-5 d-flex align-items-end">
                        <img src="<?=$this->url->to('auth','captcha')?>" alt="验证码" class="captcha-img img-thumbnail" 
                             style="cursor: pointer; height: 42px;"
                             onclick="this.src='<?=$this->url->to('auth','captcha')?>&rand='+Math.random()">
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                    <label class="form-check-label" for="remember">
                        记住登录（7天）
                    </label>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <a href="<?=$this->url->to('auth','register')?>" class="btn btn-outline-primary">
                        <i class="fa fa-user-plus me-1"></i>注册账号
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-sign-in me-1"></i>登录
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>