<?php
/**
 * 获取系统配置参数
 * 1. getSysConf支持获取 config目录下所有配置文件，可返回单个 或者 数组节点
 * 2. getEnvConf支持获取 .env文件的单个配置参数
 */

namespace core\helper;

use think\Exception;
use think\facade\Config;
use think\facade\Env;

class SysConfHelper {
    const ENV_DEFAULT = 'default';

    /**
     * 获取 config目录下指定文件名的配置，比如 database
     * @param $name
     * @return array|mixed
     * @throws Exception
     */
    public static function getFileConf($name)
    {
        self::throwEmptyError($name);
        return Config::get($name) ? Config::get($name) : [];
    }

    /**
     * 获取 .env 文件当前指定的环境，默认为开发环境
     * @return mixed|string
     */
    public static function currEnv() {
        $currEnv = Env::get('ENV');
        if(empty($currEnv)) $currEnv = 'dev';
        return $currEnv;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public static function getEnvConf($name)
    {
        self::throwEmptyError($name);
        $config = Env::get($name);
        return $config;
    }

    /**
     * 获取 system_config 文件里"区分环境"的配置，如果没有值，则再取"不区分环境"的配置
     * @param $name
     * @return mixed|string
     */
    public static function getSysConf($name)
    {
        $config = self::_getSysConf($name, self::currEnv());
        if (empty($config)) {
            $config = self::_getSysConf($name, self::ENV_DEFAULT);
        }
        return $config;
    }

    /**
     * 获取 system_config 文件里的"指定环境的配置参数
     * @param $name
     * @param $env
     * @return mixed|string
     */
    public static function _getSysConf($name, $env)
    {
        $config = self::getFileConf('system_config')[$env];
        if (empty($name)) {
            return $config;
        } elseif (isset($config[$name])) {
            return $config[$name];
        } else {
            return '';
        }
    }

    public static function throwEmptyError($key, $errInfo = '') {
        if (empty($key)) {
            if (empty($errInfo)) $errInfo = "empty param '".$key."'";
            throw new Exception($errInfo);
        }
    }
}
