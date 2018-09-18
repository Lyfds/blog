<?php
namespace controllers;
class RedbagController {
    public function init() {
        $redis = \libs\Redis::getInstance();
        $redis->set('redbag_stock',20);
        $key = 'redbag_'.date('Ymd');
        $redis->sadd($key,'-1');
        $redis->expire($key,3900);
    }
    public function makeOrder() {
        $redis = \libs\Redis::getInstance();
        $model = new \models\Redbag;
        ini_set('default_socket_timeout',-1);
        echo "开始监听红包列表... \r\n";
        while(true) {
            $date = $redis->brpop('redbag_orders',0);
            $userId = $date[1];
            $model->create($userId);
            echo "=========有人抢了红包！\r\n";
        }
    }
    public function rob() {
        if(!isset($_SESSION['id'])) {
            echo json_encode([
                'status_code' => '401',
                'message' => '未登录！'
            ]);
            exit;
        }
        if(date('H')<9 || date('H')>20) {
            echo json_encode([
                'status_code' => '403',
                'message' => '时间段不允许！'
            ]);
            exit;
        }
        $key = 'redbag_'.date('Ymd');
        $redis = \libs\Redis::getInstance();
        $exists = $redis->sismember($key,$_SESSION['id']);
        if($exists) {
            echo json_encode([
                'status_code' => '403',
                'message' => '今天已经抢过了~'
            ]);
            exit;
        }
        $stock = $redis->decr('redbag_stock');
        if($stock < 0) {
            echo json_encode([
                'status_code' => '403',
                'message' => '今天的红包已经抢完了~'
            ]);
            exit;
        }
        $redis->lpush('redbag_orders',$_SESSION['id']);
        $redis->sadd($key,$_SESSION['id']);
        echo json_encode([
            'status_code' => '200',
            'message' => '恭喜您，抢到了本站的红包~'
        ]);
    }
    public function rob_view() {
        view('redbag.rob');
    }
}