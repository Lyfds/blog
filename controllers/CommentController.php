<?php
namespace controllers;
class CommentController {
    public function comments() {
        $data = file_get_contents('php://input');
        $_POST = json_decode($data, TRUE);

        if(!isset($_SESSION['id'])) {
            echo json_encode([
               'status_code' => '401',
               'message' => '必须先登录',
            ]);
            exit;
        }
        $content = e( $_POST['content'] );
        $blog_id = $_POST['blog_id'];
        $model = new \models\Comment;
        $model->add($content,$blog_id);
        echo json_encode([
           'status_code' => '200',
           'message' => '发表成功~',
           'data' => [
               'content' => $content,
               'headimg' => $_SESSION['headimg'],
               'email' => $_SESSION['email'],
               'created_at' => date('Y-m-d H:i:s')
           ]
        ]);
        exit;
     }
     public function comment_list() {
         $blogId = $_GET['id'];
         $model = new \models\Comment;
         $data = $model->getComments($blogId);
         echo json_encode([
            'status_code' => 200,
            'data' => $data,
         ]);
     }
}