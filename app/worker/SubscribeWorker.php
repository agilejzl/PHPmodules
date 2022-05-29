<?php
/**
 * RedisQueue 的使用示范，必须实现 subscribe() 和 send ()
 */

namespace app\worker;

use Workerman\Lib\Timer;

class SubscribeWorker {
    private static $instance;
    protected static $redisUrl;
    protected static $redisClient;

    public function __construct($redisUrl = '') {
        if (empty($redisUrl)) {
            self::$redisUrl = 'redis://127.0.0.1:6379';
        }
    }

    public static function instance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
            self::$redisClient = new RedisQueue(self::$redisUrl);
        }
        return self::$instance;
    }

    /**
     * 需要实现 subscribe，订阅 XXX队列的消息
     */
    public function subscribe() {
        self::$redisClient->subscribe('userMsg', function($data){
            echo microtime(true)." userMsg: $data[0] 对 $data[1] 说 '$data[2]'\n";
        });
    }

    /**
     * 需要实现 send, 推送消息到 XXX队列
     */
    public function send()
    {
        $client = self::$redisClient;
        Timer::add(3, function()use($client){
            $client->send('userMsg', ['user1', 'user2', 'Hello']);
        });
    }
}
