<?php
namespace controller;

use core\Controller;
use model\MessageModel;
use util\Upload;
use util\Validate;

class MessageController extends Controller {
    // 留言列表页：支持分页+搜索
    public function index() {
        // 接收参数
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 当前页码（默认第1页）
        $pageSize = 10; // 每页显示条数（可配置到app.php）

        // 验证页码（防止非法值）
        if ($page < 1) $page = 1;

        // 获取留言总数和分页数据
        $messageModel = new MessageModel();
        $total = $messageModel->getMessageCount($keyword); // 总条数
        $messages = $messageModel->getMessages($page, $pageSize, $keyword); // 分页数据
        $totalPage = ceil($total / $pageSize); // 总页数

        // 传递变量到视图（含分页参数）
        $this->view->assign('messages', $messages);
        $this->view->assign('keyword', $keyword);
        $this->view->assign('page', $page);
        $this->view->assign('pageSize', $pageSize);
        $this->view->assign('total', $total);
        $this->view->assign('totalPage', $totalPage);
        
        $this->view->render('message/index');
    }

    // 发布留言页：仅登录用户可访问
    public function create() {
        $this->checkLogin(); // 登录检查

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 接收表单数据
            $content = trim($_POST['content']);
            $attachment = $_FILES['attachment']; // 附件文件

            // 数据验证
            if (!Validate::required([$content])) {
                // 跳转带分页参数（如果有）
                $this->redirect('Message', 'create', '留言内容不能为空！', 'error');
            }
            if (strlen($content) > 500) {
                $this->redirect('Message', 'create', '留言内容不能超过500字！', 'error');
            }

            // 处理附件上传
            $attachmentPath = null;
            if ($attachment['error'] === UPLOAD_ERR_OK) {
                $upload = new Upload($this->appConfig['upload_path']);
                $uploadResult = $upload->uploadFile($attachment);
                if (!$uploadResult['success']) {
                    $this->redirect('Message', 'create', $uploadResult['msg'], 'error');
                }
                $attachmentPath = $uploadResult['file_name']; // 上传成功，获取文件名
            }

            // 保存留言到数据库
            $messageModel = new MessageModel();
            $result = $messageModel->create([
                'user_id' => $this->session->get('user_id'),
                'content' => $content,
                'attachment' => $attachmentPath
            ]);

            if ($result) {
                // 发布成功跳转回列表页（保留分页和搜索参数）
                $params = [];
                if (!empty($_GET['page'])) $params['page'] = $_GET['page'];
                if (!empty($_GET['keyword'])) $params['keyword'] = $_GET['keyword'];
                $this->redirect('Message', 'index', '留言发布成功！', 'success', $params);
            } else {
                $this->redirect('Message', 'create', '留言发布失败，请重试！', 'error');
            }
        }

        // GET请求：渲染发布留言页面
        $this->view->render('message/create');
    }
	
	/**
	 * 留言修改（GET显示页面，POST处理提交）
	 */
	public function edit() {
		$this->checkLogin(); // 统一登录校验（与create方法一致）
		$messageModel = new MessageModel();
		$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
		$message = [];

		// 1. GET请求：显示修改页面（预填原有数据）
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// 校验留言ID和存在性
			if (!$id) {
				$this->redirect('Message', 'index', '留言ID无效！', 'error');
			}
			$message = $messageModel->getMessage($id);
			if (!$message) {
				$this->redirect('Message', 'index', '留言不存在！', 'error');
			}
			// 权限校验（仅作者可修改）
			if ($message['user_id'] !== $this->session->get('user_id')) {
				$this->redirect('Message', 'index', '无权限修改他人留言！', 'error');
			}
			// 传递数据到视图（与create方法的assign风格一致）
			$this->view->assign('message', $message);
			$this->view->render('message/edit');
			return;
		}

		// 2. POST请求：处理修改提交（与create方法的POST逻辑结构对齐）
		$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
		$content = trim($_POST['content']);
		$attachment = $_FILES['attachment'];

		// 2.1 基础数据验证（与create方法的验证顺序一致：必填→格式→长度）
		if (!Validate::required([$id, $content])) {
			$this->redirect('Message', 'edit', '留言ID和内容不能为空！', 'error', ['id' => $id]);
		}
		if (strlen($content) > 500) {
			$this->redirect('Message', 'edit', '留言内容不能超过500字！', 'error', ['id' => $id]);
		}

		// 2.2 二次校验：留言存在性+权限（防止POST请求篡改ID）
		$message = $messageModel->getMessage($id);
		if (!$message || $message['user_id'] !== $this->session->get('user_id')) {
			$this->redirect('Message', 'index', '无权限修改该留言！', 'error');
		}

		// 2.3 处理附件更新（复用create方法的上传逻辑）
		$updateData = ['content' => $content];
		$oldAttachment = $message['attachment'];
		if ($attachment['error'] === UPLOAD_ERR_OK) {
			$upload = new Upload($this->appConfig['upload_path']);
			$uploadResult = $upload->uploadFile($attachment);
			if (!$uploadResult['success']) {
				$this->redirect('Message', 'edit', $uploadResult['msg'], 'error', ['id' => $id]);
			}
			$updateData['attachment'] = $uploadResult['file_name'];
			// 删除旧附件（保持资源清洁）
			if ($oldAttachment) {
				$oldFilePath = $this->appConfig['upload_path'] . $oldAttachment;
				if (file_exists($oldFilePath)) {
					unlink($oldFilePath);
				}
			}
		}

		// 2.4 执行修改并跳转结果（与create方法的跳转风格一致）
		$result = $messageModel->updateMessage($id, $updateData);
		if ($result) {
			$this->redirect('Message', 'index', '留言修改成功！', 'success');
		} else {
			$this->redirect('Message', 'edit', '留言修改失败，请重试！', 'error', ['id' => $id]);
		}
	}
	
	/**
	 * 处理留言删除（需登录+权限校验）
	 */
	public function destroy() {
		$this->checkLogin(); // 登录校验
		$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
		$messageModel = new MessageModel();
		
		// 1. 校验留言存在性+权限
		if (!$id || !$messageModel->checkMessageOwner($id, $this->session->get('user_id'))) {
			$this->redirect('Message', 'index', '无权限删除该留言！', 'error');
		}
		
		// 2. 执行删除（数据库+附件文件）
		$result = $messageModel->deleteMessage($id, $this->appConfig['upload_path']);
		if ($result) {
			$this->redirect('Message', 'index', '留言删除成功！', 'success');
		} else {
			$this->redirect('Message', 'index', '留言删除失败，请重试！', 'error');
		}
	}
}