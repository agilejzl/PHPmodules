<?php

namespace core\helper;

class TimeHelper {
    /**
     * 判断当前时间是否在指定的小时、分钟内，可用于精确定时任务的时间，使用示范在 app\worker\TimerWorker
     * @param int $limitHour 是否限制小时，如果大于0，则限制。比如 8 表示只在 8点多钟中符合条件
     * @param int $startMins 是否限制大于分钟数，如果大于0，则限制
     * @param int $endMins 是否限制小于分钟数，配合startMins限制，比如 [30,60) 表示只在这个分钟内符合条件
     * @param bool $debug 不满足条件时，是否记录调试日志
     * @return bool 是否满足条件，返回 true 或者 false
     */
    public static function isInMinutes($limitHour = - 1, $startMins = 0, $endMins = 60, $debug = true){
        $bool = true;
        $funcName = '#'.debug_backtrace()[1]['function'];
        if ($limitHour >= 0 && $limitHour != (int)date('H')) {
            // LogHelper::logSpider($funcName." skipH", LogHelper::LEVEL_INFO);
            return false;
        }
        $currTimeM = (int)date('i');
        if ($currTimeM < $startMins) $bool = false;
        if ($currTimeM >= $endMins) $bool = false;

        $timeStr = $limitHour . ':' . $startMins . '~' . $endMins;
        if ($bool) {
            if($debug) LogHelper::logSpider($funcName.' enter '.$timeStr);
        } else if (!$bool) {
            if($debug) LogHelper::logSpider($funcName.' skip '.$timeStr, LogHelper::LEVEL_INFO);
        }
        return $bool;
    }

    /**
     * 判断当前时间是否在指定的秒钟内
     * @param int $startSecond
     * @param int $endSecond
     * @param bool $debug
     * @return bool
     */
    public static function isInSeconds($startSecond = 0, $endSecond = 60, $debug = false) {
        $bool = true;
        $funcName = '#'.debug_backtrace()[1]['function'];
        $currTimeS = (int)date('s');
        if ($currTimeS < $startSecond) $bool = false;
        if ($currTimeS >= $endSecond) $bool = false;

        if ($bool) {
            LogHelper::logSpider($funcName.' at second '.$currTimeS);
        } else if ($debug && !$bool) {
            LogHelper::logSpider($funcName.' skip second '.$currTimeS, LogHelper::LEVEL_DEBUG);
        }
        return $bool;
    }
}
