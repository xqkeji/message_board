<?php
namespace core;

class Captcha {
    private $width = 120;    // 验证码宽度
    private $height = 40;    // 验证码高度
    private $codeLen = 4;    // 验证码长度
	private $fontSize = 20;  // 字体大小（可调整，TTF支持任意大小）
	// 绘制验证码文字（需字体文件支持）
	private $fontFile = ROOT_PATH . '/resource/font/arial.ttf'; // 字体文件路径
    private $code;           // 生成的验证码字符串
    // 优化字符集：排除易混淆字符（0/O/1/I/l），提升可读性
    private $codeChars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';

    // 生成验证码图片并输出
    public function generate() {
        // 1. 创建图像资源
        $image = imagecreatetruecolor($this->width, $this->height);
        $bgColor = imagecolorallocate($image, 255, 255, 255); // 白色背景
        imagefill($image, 0, 0, $bgColor);

        // 2. 生成随机验证码字符串（使用优化后的字符集）
        $this->code = '';
        $charCount = strlen($this->codeChars);
        for ($i = 0; $i < $this->codeLen; $i++) {
            $this->code .= $this->codeChars[mt_rand(0, $charCount - 1)];
        }

        // 3. 绘制验证码文字（核心修复：精准坐标计算）
        $fontFile = $this->fontFile; // 字体文件路径
        if (!file_exists($fontFile)) {
            die("字体文件缺失：{$fontFile}（请下载arial.ttf放入public/css目录）");
        }

        // 关键修复1：计算字符分布（适配TTF字体，避免拥挤/贴边）
        $charWidth = $this->fontSize * 0.75; // TTF字体单字符宽度预估（字体大小的75%）
        $totalCharWidth = $this->codeLen * $charWidth; // 所有字符总宽度
        $startX = round(($this->width - $totalCharWidth) / 2); // 水平起始位置（居中分布）

        // 关键修复2：垂直居中校准（适配TTF字体基线特性）
        // imagettftext的Y坐标是文字左下角基线，需向上偏移 字体大小/4 实现垂直居中
        $baseY = round($this->height / 2 + $this->fontSize / 4);

        for ($i = 0; $i < $this->codeLen; $i++) {
            $color = imagecolorallocate($image, mt_rand(50, 120), mt_rand(50, 120), mt_rand(50, 120)); // 深色文字（对比更强）
            
            // 关键修复3：精准X坐标（均匀分布+小范围偏移，避免重叠）
            $xOffset = mt_rand(-2, 2); // 水平偏移±2px（可控，不超出画布）
            $x = (int)($startX + $i * $charWidth + $xOffset);

            // 关键修复4：精准Y坐标（居中+小范围偏移，避免贴顶/贴底）
            $yOffset = mt_rand(-3, 3); // 垂直偏移±3px（可控）
            $y = (int)($baseY + $yOffset);

            // 关键修复5：缩小旋转角度（±15°，避免旋转过度导致超出画布）
            $angle = mt_rand(-15, 15);

            // 绘制文字（TTF字体，位置精准）
            imagettftext($image, $this->fontSize, $angle, $x, $y, $color, $fontFile, $this->code[$i]);
        }

        // 4. 添加干扰线（5条，线宽1-2px，不遮挡文字）
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, mt_rand(150, 200), mt_rand(150, 200), mt_rand(150, 200));
            imagesetthickness($image, mt_rand(1, 2)); // 线宽可控
            // 线条起点/终点限制在画布内
            imageline(
                $image,
                (int)mt_rand(0, $this->width / 4),
                (int)mt_rand(0, $this->height),
                (int)mt_rand($this->width * 3 / 4, $this->width),
                (int)mt_rand(0, $this->height),
                $lineColor
            );
        }

        // 5. 添加干扰点（50个，轻度干扰）
        for ($i = 0; $i < 50; $i++) {
            $dotColor = imagecolorallocate($image, mt_rand(180, 220), mt_rand(180, 220), mt_rand(180, 220));
            imagesetpixel(
                $image,
                (int)mt_rand(0, $this->width),
                (int)mt_rand(0, $this->height),
                $dotColor
            );
        }

        // 6. 输出图像（禁止缓存）
        header('Content-Type: image/png');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        imagepng($image);
        imagedestroy($image); // 销毁图像资源，释放内存
		$container=Container::getInstance();
		$session=$container->get('session');
        // 7. 保存验证码到会话（小写存储，忽略大小写验证）
        $session->set('captcha', strtolower($this->code));
    }

    // 验证验证码：返回布尔值
    public static function check($input) {
		$container=Container::getInstance();
		$session=$container->get('session');
        $captcha = $session->get('captcha');
        if (!$captcha) return false; // 会话中无验证码
        
        $session->delete('captcha'); // 验证一次后失效，防止重复使用
        return strtolower(trim($input)) === $captcha;
    }

    // 可选：自定义配置（灵活调整尺寸/字体大小，位置自动适配）
    public function setConfig($config = []) {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key) && $value > 0) {
                switch ($key) {
                    case 'width':
                        $this->width = max(100, min(200, $value));
                        break;
                    case 'height':
                        $this->height = max(30, min(60, $value));
                        break;
                    case 'codeLen':
                        $this->codeLen = max(3, min(5, $value));
                        break;
                    case 'fontSize':
                        $this->fontSize = max(16, min(30, $value));
                        // 字体放大后自动调整画布高度（避免文字超出）
                        $this->height = round($this->fontSize * 2);
                        break;
                }
            }
        }
    }
}