-- ----------------------------
-- 数据库设计说明
-- 创建数据库
-- ----------------------------
CREATE DATABASE IF NOT EXISTS message_board 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;
USE message_board;
-- ----------------------------
-- 用户表（存储账号信息，用户名/邮箱唯一）
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int NOT NULL AUTO_INCREMENT,-- 用户id自动增长
  `username` varchar(50) NOT NULL, -- 用户名（唯一）
  `email` varchar(100) NOT NULL,  -- 邮箱（唯一）
  `password` varchar(255) NOT NULL,-- 加密后的密码
  `remember_token` varchar(255) NULL, -- 记住登录的token
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,-- 用户注册日期
  PRIMARY KEY (`id`),-- 用户id主键索引
  UNIQUE INDEX `username`(`username`), -- 用户名唯一索引
  UNIQUE INDEX `email`(`email`)       -- 邮箱唯一索引
) ENGINE = InnoDB;
-- ----------------------------
-- 插入用户测试数据
-- ----------------------------
INSERT INTO `user` VALUES (1, 'test1', 'test1@qq.com', '$2y$12$cQ7CWzJH1QM0LS80E.I/E.A5Lh1ASgB6DPZdQD11ys6da86N9yomK', NULL, '2025-11-02 16:28:44');

-- ----------------------------
-- 留言表：存储留言信息（关联用户表）
-- ----------------------------
DROP TABLE IF EXISTS `message`;
CREATE TABLE `message`  (
  `id` int NOT NULL AUTO_INCREMENT,-- 留言id自动增长
  `user_id` int NOT NULL, -- 关联用户ID（外键）
  `content` text NOT NULL,-- 留言内容
  `attachment` varchar(255) NULL, -- 附件路径（可选）
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),-- 留言id主键索引
  INDEX `user_id`(`user_id`),-- 用户id普通索引
  -- 外键关联：删除用户时自动删除其留言
  CONSTRAINT `message_user_id` FOREIGN KEY (`user_id`) 
  REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB;
-- ----------------------------
-- 插入留言测试数据
-- ----------------------------
INSERT INTO `message` VALUES (1, 1, '测试留言1', NULL, '2025-11-04 13:28:20');
INSERT INTO `message` VALUES (2, 1, '测试留言2', NULL, '2025-11-04 13:28:42');
INSERT INTO `message` VALUES (3, 1, '测试留言3', NULL, '2025-11-04 13:28:52');
INSERT INTO `message` VALUES (4, 1, '测试留言4', '20251104052921_69098f317559b.png', '2025-11-04 13:29:21');
INSERT INTO `message` VALUES (5, 1, '测试留言5', NULL, '2025-11-04 13:29:46');
INSERT INTO `message` VALUES (6, 1, '测试留言6', NULL, '2025-11-04 13:29:55');
INSERT INTO `message` VALUES (7, 1, '测试留言7', '20251104053016_69098f68c1525.png', '2025-11-04 13:30:16');
INSERT INTO `message` VALUES (8, 1, '测试留言8', NULL, '2025-11-04 13:30:34');
INSERT INTO `message` VALUES (9, 1, '测试留言9', NULL, '2025-11-04 13:30:42');
INSERT INTO `message` VALUES (10, 1, '测试留言10', NULL, '2025-11-04 13:30:49');
INSERT INTO `message` VALUES (11, 1, '测试留言11', NULL, '2025-11-04 13:30:57');
INSERT INTO `message` VALUES (12, 1, '测试留言12', '20251104053107_69098f9b81f4c.png', '2025-11-04 13:31:07');
INSERT INTO `message` VALUES (13, 1, '测试留言13', NULL, '2025-11-05 09:31:00');
INSERT INTO `message` VALUES (14, 1, '测试留言14', '20251105013114_690aa8e293d3f.png', '2025-11-05 09:31:14');