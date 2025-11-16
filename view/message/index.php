<?php $this->title = '留言列表'; ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="card-title mb-0">
                    <i class="fa fa-comments text-primary me-2"></i>留言列表
                    <span class="badge bg-secondary ms-2"><?php echo $total; ?> 条留言</span>
                </h3>
            </div>
            <div class="col-md-6">
                <!-- 搜索表单：保留分页参数 -->
                <form method="get" action="<?=$this->url->to('message','index')?>" class="d-flex gap-2">
                    <?php if (!empty($page) && $page > 1): ?>
                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <input type="text" name="keyword" class="form-control" 
                                   placeholder="搜索留言内容..." value="<?php echo $keyword; ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <?php if ($keyword): ?>
                        <a href="<?=$this->url->to('message','index')?>" class="btn btn-outline-danger">
                            <i class="fa fa-times"></i> 清空
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (empty($messages)): ?>
    <div class="alert alert-info text-center py-5">
        <i class="fa fa-info-circle text-3xl mb-3"></i>
        <h4 class="alert-heading"><?php echo $keyword ? '没有找到相关留言' : '暂无留言'; ?></h4>
        <p class="mb-4">
            <?php echo $keyword ? '换个关键词试试吧' : '快来发布第一条留言，分享你的想法！'; ?>
        </p>
        <?php if ($this->session->get('user_id')): ?>
            <a href="<?=$this->url->to('message','create')?>" class="btn btn-primary btn-lg">
                <i class="fa fa-pencil me-2"></i>发布留言
            </a>
        <?php else: ?>
            <a href="<?=$this->url->to('auth','login')?>" class="btn btn-primary btn-lg">
                <i class="fa fa-sign-in me-2"></i>登录后发布
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row gap-4">
        <?php foreach ($messages as $message): ?>
            <div class="col-12">
                <div class="card message-card h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold text-primary"><?php echo $message['username']; ?></span>
                            <small class="text-muted ms-2">
                                <i class="fa fa-clock-o me-1"></i><?php echo date('Y-m-d H:i:s', strtotime($message['created_at'])); ?>
                            </small>
                        </div>
                        <span class="badge bg-secondary">
                            #<?php echo $message['id']; ?>
                        </span>
                    </div>
                    <!-- 卡片体（留言内容+附件） -->
					<div class="card-body">
						<!-- 原有留言内容和附件代码 -->
						<?php echo nl2br(htmlspecialchars($message['content'])); ?>
						<?php if ($message['attachment']): ?>
							<div class="mt-3 pt-3 border-top">
                                <a href="<?php echo $this->url->upload($message['attachment']); ?>" 
                                   target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-download me-1"></i>
                                    下载附件：<?php echo $message['attachment']; ?>
                                </a>
                            </div>
						<?php endif; ?>
						
						<!-- 新增：操作按钮（仅留言作者显示） -->
						<?php if ($this->session->get('user_id') == $message['user_id']): ?>
							<div class="mt-3 pt-3 border-top d-flex gap-2">
								<a href="<?=$this->url->to('message','edit', ['id' => $message['id']])?>" 
								   class="btn btn-outline-primary btn-sm">
									<i class="fa fa-edit me-1"></i>修改
								</a>
								<a href="<?=$this->url->to('message','destroy', ['id' => $message['id']])?>" 
								   class="btn btn-outline-danger btn-sm"
								   onclick="return confirm('确定要删除这条留言吗？删除后不可恢复！')">
									<i class="fa fa-trash me-1"></i>删除
								</a>
							</div>
						<?php endif; ?>
					</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 分页控件 -->
    <?php if ($totalPage > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <!-- 上一页 -->
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?=$this->url->page($page-1)?>">
                        上一页
                    </a>
                </li>

                <!-- 页码导航（最多显示5个页码） -->
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPage, $startPage + 4);
                if ($endPage - $startPage < 4) {
                    $startPage = max(1, $endPage - 4);
                }
                ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?=$this->url->page($i)?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- 下一页 -->
                <li class="page-item <?php echo $page >= $totalPage ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?=$this->url->page($page+1)?>">
                        下一页
                    </a>
                </li>
            </ul>
            <div class="text-center mt-2 text-muted">
                第 <?php echo $page; ?> / <?php echo $totalPage; ?> 页 · 每页 <?php echo $pageSize; ?> 条
            </div>
        </nav>
    <?php endif; ?>
<?php endif; ?>