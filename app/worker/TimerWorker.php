<?php
/**
 * Server启动命令：php think worker:server
 */
namespace app\worker;

use core\helper\LogHelper;
use core\helper\TimeHelper;
use think\worker\Server;
use Workerman\Lib\Timer;

class TimerWorker extends Server {

    /**
     * 实现 onWorkerStart
     * @param $worker
     * @throws \Exception
     */
    public function onWorkerStart($worker) {
        LogHelper::logSpider("onWorkerStart");
        Timer::add(60*3, [self::class, 'monitorServer']);
        Timer::add(60*5, [self::class, 'runAtHour']);
        Timer::add(60-1, [self::class, 'runAtMinutes']);
        Timer::add(1, [self::class, 'runAtSeconds']);

        $grabTime = (new \DateTime())->add(new \DateInterval('PT' . 1 . 'M'));
        $timeStr = $grabTime->format('H:i:s');
        LogHelper::logSpider("Timer4: 抢购将在60秒后，".$timeStr." 开始");
        Timer::add(1, function()use($worker, $grabTime) {
            self::onTimeGrab($grabTime);
        });

        SubscribeWorker::instance()->subscribe();
        SubscribeWorker::instance()->send();
    }

    public static function onTimeGrab($grabTime) {
        $timeStr = $grabTime->format('H:i:s');
        $timeArr = explode(':', $timeStr);
        if (TimeHelper::isInMinutes($timeArr[0], $timeArr[1], $timeArr[1]+1, false)
            && TimeHelper::isInSeconds($timeArr[2], $timeArr[2]+1)) {
            LogHelper::logSpider("Timer4: 现在是 ".$timeStr."，开启抢购!");
        }
    }

    public static function monitorServer() {
        if (TimeHelper::isInMinutes(-1)) {
            LogHelper::logSpider("Timer0: 无时间限制, 开始执行");
        }
    }

    public static function runAtHour() {
        if (TimeHelper::isInMinutes(8)) {
            LogHelper::logSpider("Timer1: 现在是8点多钟, 开始执行");
        }
    }

    public static function runAtMinutes() {
        if (TimeHelper::isInMinutes(-1, 0, 1)) {
            LogHelper::logSpider("Timer2: 在每小时的0-1分, 开始执行");
        }
    }

    public static function runAtSeconds() {
        if (TimeHelper::isInSeconds(0, 1)) {
            LogHelper::logSpider("Timer3: 在每分钟的0-1秒, 开始执行");
        }
    }
}

