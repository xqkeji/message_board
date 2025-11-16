<?php $this->title = '发布留言'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card p-4 p-md-5">
            <h2 class="card-title text-center mb-4">
                <i class="fa fa-pencil text-primary me-2"></i>发布留言
            </h2>
            <!-- 表单action改为新格式，保留分页和搜索参数 -->
            <form method="post" action="<?=$this->url->to('message','create',$_GET)?>" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="content" class="form-label">留言内容</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required
                              placeholder="分享你的想法...（最多500字）" maxlength="500"></textarea>
                    <div class="form-text">
                        <span id="charCount">0</span>/500 字
                    </div>
                </div>

                <div class="mb-4">
                    <label for="attachment" class="form-label">上传附件（可选）</label>
                    <input class="form-control" type="file" id="attachment" name="attachment"
                           accept="image/jpeg,image/png,image/gif,application/pdf,application/msword">
                    <div class="form-text">
                        支持格式：jpg、png、gif、pdf、doc | 最大2MB
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?=$this->url->to('message','create',$_GET)?>" class="btn btn-outline-secondary me-md-2">
                        <i class="fa fa-arrow-left me-1"></i>返回列表
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-paper-plane me-1"></i>发布留言
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // 字符计数
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        
        if (content && charCount) {
            // 初始化计数
            charCount.textContent = content.value.length;
            
            content.addEventListener('input', function() {
                const length = this.value.length;
                charCount.textContent = length;
                
                if (length > 500) {
                    this.value = this.value.substring(0, 500);
                    charCount.textContent = 500;
                }
                
                // 超过450字时变色提醒
                if (length > 450) {
                    charCount.classList.add('text-danger');
                } else {
                    charCount.classList.remove('text-danger');
                }
            });
        }
    });
</script>