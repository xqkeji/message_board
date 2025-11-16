<?php $this->title = '修改密码'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card p-4 p-md-5">
            <h2 class="card-title text-center mb-4">
                <i class="fa fa-key text-primary me-2"></i>修改密码
            </h2>
            <form method="post" action="<?=$this->url->to('auth','change-password')?>">
                <div class="row mb-3">
                    <label for="old_password" class="col-sm-3 col-form-label">原密码</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="old_password" name="old_password" required
                                   placeholder="请输入原密码">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="new_password" class="col-sm-3 col-form-label">新密码</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-shield"></i>
                            </span>
                            <input type="password" class="form-control" id="new_password" name="new_password" required
                                   placeholder="至少6位字符">
                        </div>
                        <div class="form-text">密码长度不能少于6位，建议包含字母和数字</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label for="confirm_password" class="col-sm-3 col-form-label">确认新密码</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-check-circle"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                   placeholder="再次输入新密码">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?=$this->url->to('message','index')?>" class="btn btn-outline-secondary me-md-2">取消</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>保存修改
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>