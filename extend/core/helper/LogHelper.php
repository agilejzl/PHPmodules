<?php
/**
 * 业务日志记录模块，支持以下主要功能
 * 1.区分业务类型，分别记录到不同的日志文件，方便后续追踪问题状况
 * 2.可配置最低日志级别，低于该级别不会写入到日志，默认的 LOG_LEVEL 为 INFO级别
 * 3.可记录异常的详细信息，代码行、堆栈信息，使用示范 $errInfo = LogHelper::detailError($e, true);
 * 使用示范 LogHelper::logDebug($errInfo, LogHelper::LEVEL_ERROR);
 */

namespace core\helper;

use think\facade\Env;

class LogHelper
{
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARN = 'WARN';
    const LEVEL_ERROR = 'ERROR';
    const LEVELS = ['DEBUG' => 0, 'INFO' => 1, 'WARN' => 2, 'ERROR' => 3];

    public static function shouldLog($logLevel) {
        $minLevel = Env::get('LOG_LEVEL');
        if (empty($minLevel)) $minLevel = self::LEVEL_INFO;
        if (isset(self::LEVELS[$logLevel])) {
            return self::LEVELS[$logLevel] >= self::LEVELS[$minLevel];
        } else {
            LogHelper::logSpider('Invalid logLevel: '.$logLevel, self::LEVEL_ERROR);
            return true;
        }
    }

    public static function detailError($e, $traceInfo = false) {
        $errInfo = $e->getMessage().'['.$e->getFile().'.'.$e->getLine().']';
        if ($traceInfo) {
            $errInfo = $errInfo.'\n'.$e->getTraceAsString();
        }
        return $errInfo;
    }

    public static function logDebug($logContent, $logLevel = self::LEVEL_INFO) {
        self::logByFilename('debug.log', $logContent, $logLevel);
    }

    public static function logSpider($logContent, $logLevel = self::LEVEL_INFO) {
        self::logByFilename('spider.log', $logContent, $logLevel);
    }

    public static function logByFilename($filename, $logContent, $logLevel = self::LEVEL_INFO) {
        if (!self::shouldLog($logLevel)) return false;
        $logDir = root_path() . 'runtime/log/';   // Env::get('ROOT_PATH') 包含了 public子目录
        !is_dir($logDir) && mkdir($logDir, 0755, true);

        if (is_array($logContent)) {
            // json_encode 支持记录中文等特殊字符
            $logContent = json_encode($logContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        list($usec, $sec) = explode(' ', microtime());
        $mssec = sprintf('%.3f', floatval($usec)) * 1000;
        $logPrefix = PHP_EOL.'[' . (new \DateTime)->format('m-d H:i:s.'). $mssec . '] ' . $logLevel;
        file_put_contents($logDir . $filename, $logPrefix.' '.$logContent, FILE_APPEND);
    }
}
