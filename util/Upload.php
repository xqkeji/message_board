<?php
namespace util;

class Upload {
    private $uploadPath;    // 上传目录路径
    private $allowedTypes;  // 允许上传的文件类型
    private $maxSize;       // 最大文件大小（默认2MB）

    public function __construct($uploadPath) {
        $this->uploadPath = $uploadPath;
        // 允许的文件类型（MIME类型）
        $this->allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf', 'application/msword'
        ];
        $this->maxSize = 2 * 1024 * 1024; // 2MB

        // 检查并创建上传目录
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true); // 递归创建目录
        }
    }

    // 上传文件：接收$_FILES数组，返回结果数组
    public function uploadFile($file) {
        // 1. 检查文件上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error('文件上传失败：' . $this->getErrorMsg($file['error']));
        }

        // 2. 检查文件类型
        if (!in_array($file['type'], $this->allowedTypes)) {
            return $this->error('不允许的文件类型！支持：jpg、png、gif、pdf、doc');
        }

        // 3. 检查文件大小
        if ($file['size'] > $this->maxSize) {
            return $this->error('文件大小超过限制！最大支持2MB');
        }

        // 4. 生成唯一文件名（避免冲突）
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION); // 获取文件扩展名
        $fileName = date('YmdHis') . '_' . uniqid() . '.' . $ext; // 文件名格式：日期_随机字符串.扩展名
        $targetPath = $this->uploadPath . $fileName; // 目标路径

        // 5. 移动临时文件到上传目录
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'file_name' => $fileName, // 返回文件名（存储到数据库）
                'msg' => '文件上传成功'
            ];
        } else {
            return $this->error('文件移动失败！请检查目录权限');
        }
    }

    // 错误结果格式化
    private function error($msg) {
        return [
            'success' => false,
            'msg' => $msg
        ];
    }

    // 上传错误码对应信息
    private function getErrorMsg($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '上传文件超过了php.ini限制',
            UPLOAD_ERR_FORM_SIZE => '上传文件超过了表单限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '缺少临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => '文件上传被扩展阻止'
        ];
        return $errors[$errorCode] ?? '未知错误（错误码：' . $errorCode . '）';
    }
}