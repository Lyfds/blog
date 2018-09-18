<?php
namespace models;
class Comment extends Base {
    public function add($content,$blog_id) {
        $stmt = self::$pdo->prepare('INSERT INTO comments(content,blog_id,user_id) VALUES(?,?,?)');
        $stmt->execute([
            $content,
            $blog_id,
            $_SESSION['id']
        ]);
    }
}