<?php
namespace controllers;

class IndexController
{
    public function index()
    {
        $blog = new \models\Blog;
        $blogs = $blog->getNew();

        $user = new \models\User;
        $users = $user->getActiveUsers();
        view('index.index', [
            'blogs' => $blogs,
            'users' => $users
        ]);
    }
}