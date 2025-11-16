<?php $this->title = '修改留言'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8"> <!-- 与create.php保持相同的布局宽度 -->
        <div class="card p-4 p-md-5">
            <h2 class="card-title text-center mb-4">
                <i class="fa fa-edit text-primary me-2"></i>修改留言
            </h2>
            <!-- 表单提交地址指向自身（与create.php一致） -->
            <form method="post" action="<?=$this->url->to('message','edit')?>" enctype="multipart/form-data">
                <!-- 隐藏域：传递留言ID -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($this->message['id']); ?>">
                
                <!-- 留言内容（预填原有内容，样式与create.php一致） -->
                <div class="mb-4">
                    <label for="content" class="form-label">留言内容</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required
                              placeholder="分享你的想法...（最多500字）" maxlength="500">
<?php echo htmlspecialchars($this->message['content']); ?></textarea>
                    <div class="form-text">
                        <span id="charCount" class="text-muted">
                            <?php echo strlen($this->message['content']); ?>
                        </span>/500 字
                    </div>
                </div>

                <!-- 附件上传（样式与create.php一致，新增原有附件显示） -->
                <div class="mb-4">
                    <label for="attachment" class="form-label">上传附件（可选，重新上传将覆盖原附件）</label>
                    <input class="form-control" type="file" id="attachment" name="attachment"
                           accept="image/jpeg,image/png,image/gif,application/pdf,application/msword">
                    <!-- 显示原有附件（新增逻辑，样式保持简洁） -->
                    <?php if (!empty($this->message['attachment'])): ?>
                        <div class="mt-2 alert alert-info">
                            <i class="fa fa-file-o me-1"></i>当前附件：
                            <a href="<?php echo $this->url->upload($this->message['attachment']); ?>" target="_blank">
                                <?php echo htmlspecialchars($this->message['attachment']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        支持格式：jpg、png、gif、pdf、doc | 最大2MB <!-- 与create.php的限制一致 -->
                    </div>
                </div>

                <!-- 按钮组（样式、图标与create.php对齐） -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?=$this->url->to('message','index')?>" class="btn btn-outline-secondary me-md-2">
                        <i class="fa fa-arrow-left me-1"></i>取消
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>保存修改
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 字符计数脚本（完全复用create.php的逻辑，保持一致） -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        if (content && charCount) {
            charCount.textContent = content.value.length;
            content.addEventListener('input', function() {
                let length = this.value.length;
                if (length > 500) {
                    this.value = this.value.substring(0, 500);
                    length = 500;
                }
                charCount.textContent = length;
                charCount.classList.toggle('text-danger', length > 450);
            });
        }
    });
</script>