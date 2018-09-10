<?php
namespace controllers;

// 引入模型类
use models\User;
use models\Order;

class UserController
{
    public function docharge()
    {
        // 生成订单
        $money = $_POST['money'];
        $model = new Order;
        $model->create($money);
        message('充值订单已生成，请立即支付！', 2, '/user/orders');
    }

    // 订单列表
    public function orders()
    {
        $order = new Order;
        // 搜索数据
        $data = $order->search();

        // 加载视图
        view('users.order', $data);
    }
    
    public function charge()
    {
        view('users.charge');
    }

    public function logout()
    {
        // 清空 SESSION
        $_SESSION = [];

        // 跳转
        message('退出成功', 2, '/');
    }

    // 处理登录的表单
    public function dologin()
    {
        // 接收表单
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        // 使用模型登录
        $user = new User;
        if( $user->login($email, $password) )
        {
            message('登录成功！', 2, '/blog/index');
        }
        else
        {
            message('账号或者密码错误', 1, '/user/login');
        }
    }

    public function login()
    {
        view('users.login');
    }

    public function register()
    {
        // 显示视图
        view('users.add');
    }

    public function hello()
    {
        // 取数据
        $user = new User;
        $name = $user->getName();

        // 加载视图
        view('users.hello', [
            'name' => $name
        ]);
    }

    public function world()
    {
        echo 'world';
    }

    public function store()
    {
        // 1. 接收表单
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        // 2. 生成激活码(32位的随机的字符串)（原则：让用户猜不出来规律）
        $code = md5( rand(1,99999) );

        // 3. 保存到 Redis
        $redis = \libs\Redis::getInstance();
        // 序列化（数组转成 JSON 字符串）
        $value = json_encode([
            'email' => $email,
            'password' => $password,
        ]);
        // 键名
        $key = "temp_user:{$code}";
        $redis->setex($key, 300, $value);

        // 4. 把激活码发送到用户的账邮箱中
        // 从邮箱地址中取出姓名 
        $name = explode('@', $email);
        // 构造收件人地址[    fortheday@126.com   ,    fortheday  ]
        $from = [$email, $name[0]];
        // 构造消息数组
        $message = [
            'title' => '智聊系统-账号激活',
            'content' => "点击以下链接进行激活：<br> 点击激活：
            <a href='http://localhost:9999/user/active_user?code={$code}'>
            http://localhost:9999/user/active_user?code={$code}</a><p>
            如果按钮不能点击，请复制上面链接地址，在浏览器中访问来激活账号！</p>",
            'from' => $from,
        ];
        // 把消息转成字符串(JSON ==> 序列化)
        $message = json_encode($message);
        // 放到队列中
        $redis = \libs\Redis::getInstance();
        $redis->lpush('email', $message);

        echo 'ok';

    }

    public function active_user()
    {
        // 1. 接收激活码
        $code = $_GET['code'];

        // 2. 到 Redis 取出账号
        $redis = \libs\Redis::getInstance();
        // 拼出名字
        $key = 'temp_user:'.$code;
        // 取出数据
        $data = $redis->get($key);
        // 判断有没有
        if($data)
        {
            // 从 redis 中删除激活码
            $redis->del($key);
            // 反序列化（转回数组）
            $data = json_decode($data, true);
            // 插入到数据库中
            $user = new \models\User;
            $user->add($data['email'], $data['password']);
            // 跳转到登录页面
            header('Location:/user/login');
        }
        else
        {
            die('激活码无效！');
        }
    }
}