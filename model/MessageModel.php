<?php
namespace model;

use core\Model;

class MessageModel extends Model {
    protected $table = 'message'; // 对应数据表名

    // 发布留言：接收user_id、content、attachment，返回受影响行数
    public function create($data) {
        return $this->insert($data);
    }

    // 获取留言总数（支持关键词搜索）- 用于分页
    public function getMessageCount($keyword = '') {
        if ($keyword) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE content LIKE :keyword";
            $result = $this->find($sql, [':keyword' => "%{$keyword}%"]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->find($sql);
        }
        return $result['total'];
    }

    // 获取留言列表（支持分页+关键词搜索）
    public function getMessages(int $page = 1,int $pageSize = 10,string $keyword = '') {
        $offset = ($page - 1) * $pageSize; // 计算偏移量
        
        if ($keyword) {
            $sql = "SELECT m.*, u.username FROM {$this->table} m 
                    LEFT JOIN user u ON m.user_id = u.id 
                    WHERE m.content LIKE :keyword 
                    ORDER BY m.created_at DESC 
                    LIMIT :offset , :pageSize";
            return $this->select($sql, [
                ':keyword' => "%{$keyword}%",
                ':offset' => (int)$offset,
                ':pageSize' => (int)$pageSize
            ]);
        } else {
            $sql = "SELECT m.*, u.username FROM {$this->table} m 
                    LEFT JOIN user u ON m.user_id = u.id 
                    ORDER BY m.created_at DESC 
                    LIMIT :offset,:pageSize";
            return $this->select($sql, [
                ':offset' => (int)$offset,
                ':pageSize' =>(int)$pageSize
            ]);
        }
    }

    // 根据用户ID获取留言（可选功能）
    public function getUserMessages($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->select($sql, [':user_id' => $userId]);
    }
	/**
	 * 根据留言ID获取单条留言信息（仅查询留言表，不关联用户表）
	 * @param int $id 留言ID
	 * @return array|null 留言表原始字段数据，不存在则返回null
	 */
	public function getMessage(int $id): array|null {
		$sql = "SELECT * FROM {$this->table} WHERE id = :id";
		// 预处理查询，仅绑定留言ID参数（防SQL注入）
		return $this->find($sql, [':id' => $id]);
	}
	/**
	 * 修改留言（支持更新内容和附件路径）
	 * @param int $id 留言ID
	 * @param array $data 待更新数据（content必传，attachment可选）
	 * @return int 受影响行数
	 */
	public function updateMessage(int $id, array $data): int {
		$setClause = [];
		$params = [];
		// 拼接更新字段（content必传，attachment可选）
		if (isset($data['content'])) {
			$setClause[] = 'content = :content';
			$params[':content'] = $data['content'];
		}
		if (isset($data['attachment'])) {
			$setClause[] = 'attachment = :attachment';
			$params[':attachment'] = $data['attachment'];
		}
		$params[':id'] = $id; // 留言ID（条件）
		
		$sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
		return $this->query($sql, $params)->rowCount();
	}
	/**
	 * 删除留言（同时删除服务器上的附件文件）
	 * @param int $id 留言ID
	 * @param string $uploadPath 附件存储目录绝对路径
	 * @return bool 删除结果
	 */
	public function deleteMessage(int $id, string $uploadPath): bool {
		// 1. 先查询留言信息（获取附件路径）
		$message = $this->find("SELECT attachment FROM {$this->table} WHERE id = :id", [':id' => $id]);
		if (!$message) return false;
		
		// 2. 删除数据库记录
		$sql = "DELETE FROM {$this->table} WHERE id = :id";
		$this->query($sql, [':id' => $id]);
		
		// 3. 若有附件，删除服务器上的文件
		if (!empty($message['attachment'])) {
			$filePath = $uploadPath . $message['attachment'];
			if (file_exists($filePath)) {
				unlink($filePath); // 删除文件
			}
		}
		return true;
	}
	/**
	 * 校验留言所有权（判断当前用户是否是留言作者）
	 * @param int $id 留言ID
	 * @param int $userId 登录用户ID
	 * @return bool 校验结果
	 */
	public function checkMessageOwner(int $id, int $userId): bool {
		$message = $this->find(
			"SELECT id FROM {$this->table} WHERE id = :id AND user_id = :user_id",
			[':id' => $id, ':user_id' => $userId]
		);
		return !empty($message);
	}
}