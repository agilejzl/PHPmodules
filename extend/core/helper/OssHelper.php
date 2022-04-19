<?php

namespace core\helper;

use OSS\Core\OssException;
use OSS\OssClient;
use think\Exception;

require_once dirname(dirname(dirname(__DIR__))).'/vendor/aliyuncs/oss-sdk-php/autoload.php';

class OssHelper
{
    private static $ossConf;
    private static $instance;

    public static function getClient() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * OssHelper constructor.
     * @throws Exception
     * @throws OssException
     */
    public function __construct() {
        self::$ossConf = SysConfHelper::getEnvConf('oss');
        if (empty(self::$ossConf)) {
            throw new Exception('Invalid OSS config');
        } else {
            self::$client = new OssClient(self::$ossConf['access_key_id'], self::$ossConf['access_key_secret'], self::$ossConf['endpoint']);
        }
    }

    /**
     * @param $ossDir
     * @param $ossFilePath
     * @param $localFilePath
     * @return mixed
     */
    public static function ossUpload($ossDir, $ossFilePath, $localFilePath)
    {
        LogHelper::logDebug("Ready upload by ossDir: ".$ossDir.", ossFilePath: ".$ossFilePath.", localFilePath: ".$localFilePath);
        $ossClient = self::getClient();
        $ossClient->createObjectDir(self::$ossConf['bucket'], $ossDir); // 创建OSS虚拟文件夹
        $result = $ossClient->uploadFile(self::$ossConf['bucket'], $ossFilePath, $localFilePath);
        LogHelper::logDebug("Upload result: ".$result['oss-request-url']);
        // LogHelper::logUpload("Upload result: ".json_encode($result));
        return $result;
    }

    /**
     * 下载文件
     * @param $object  // oss存储对象
     * @param $localFilePath // 下载存储路径
     * @throws OssException
     */
    public static function getObject($object, $localFilePath)
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localFilePath
        );
        self::getClient()->getObject(self::$ossConf['bucket'], $object, $options);
    }

    /**
     * 生成签名 获取url
     * @param $url
     * @param int $expireIn
     * @return string
     */
    public static function signatureUrl($url, $expireIn = 3600)
    {
        $url = urldecode($url);
        $fileName = StringHepler::cut_str($url, '/', -1);
        $path = str_replace($fileName, '', $url);
        $path = str_replace(self::$ossConf['domain'], '', $path);
        $expire = time() + $expireIn;

        $StringToSign = "GET\n\n\n" . $expire . "\n/" . self::$ossConf['bucket'] . "/" . $path . $fileName;
        $Sign = base64_encode(hash_hmac("sha1", $StringToSign, self::$ossConf['access_key_secret'], true));
        $url = self::$ossConf['domain'] . $path . urlencode($fileName) . "?OSSAccessKeyId=" . self::$ossConf['access_key_id'] . "&Expires=" . $expire . "&Signature=" . urlencode($Sign);
        return $url;
    }

    /**
     * @param $url
     * @param $origin_name
     */
    public static function downloadFile($url, $origin_name)
    {
        $headers = get_headers($url, true); // $url就是文件的全路径 注意是全路径 get_headers函数会打印出oss文件的详细信息
        $size = $headers['Content-Length'];
        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename = " . $origin_name);
        header("Accept-ranges:bytes");
        header("Accept-length:" . $size);
        readfile($url);
    }
}
