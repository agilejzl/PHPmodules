<?php

namespace core\uploader;

use core\helper\LogHelper;
use core\helper\OssHelper;
use OSS\Core\OssException;
use think\Exception;
use think\facade\Env;

class BaseUploader
{
    protected static $model;
    protected static $mountedAs = '';
    protected static $tempFile;

    // 默认的上传参数配置
    public static $configs = [
        'storage' => 'file',
        'allowedExts' => '*',
        'storeDir' => '',
        'delTempFile' => true,
        'maxSize' => 1 * 1024 * 1024
    ];

    protected function storage() {
        return self::$configs['storage'];
    }

    protected function allowedFileExts() {
        return self::$configs['allowedExts'];
    }

    protected function maxFileSize() {
        return self::$configs['maxSize']; // default limit 1MB
    }

    /**
     * @return string 用于本地临时文件和远程存储的目录
     */
    protected function storeDir() {
        if (empty(self::$configs['storeDir'])) {
            return '/'.self::$mountedAs;
        } else {
            return self::$configs['storeDir'];
        }
    }

    protected function filename() {
        if (self::$tempFile) {
            // LogHelper::logDebug("filename ".self::$tempFile->getFilename());
            return self::$tempFile->getFilename();
        } else {
            LogHelper::logDebug("tempFile is empty!", LogHelper::LEVEL_WARN);
            return '';
        }
    }

    protected function localBaseDir() {
        return 'public/upload'; // 默认缓存目录
    }

    /**
     * @return string 本地临时文件保存在这个目录
     */
    protected function localTempDir() {
        return Env::get('ROOT_PATH').$this->localBaseDir().$this->storeDir();
    }

    protected function tempFilePath() {
        if (empty(self::$tempFile)) {
            return '';
        } else {
            return $this->localTempDir().'/'.str_replace('\\', '/', self::$tempFile->getSaveName());
        }
    }

    protected function validStorage() {
        return $this->storage() == 'file' || $this->storage() == 'oss';
    }

    /**
     * BaseUploader constructor.
     * @param array $config
     * @param array $model
     * @throws Exception
     */
    public function __construct($config = [], $model = []) {
        self::$configs = array_merge(self::$configs, $config);

        // $clazz = get_called_class();
        // LogHelper::logDebug("new clazz: ".$clazz);
        self::$model = $model;
        // if (empty(self::$mountedAs)) throw new Exception('Unknown $mountedAs');
        if (!self::validStorage()) throw new Exception("Unsupported storage '".$this->storage()."'");
    }

    public function openAsThinkFile($filePath) {
        $explodeArr = explode('/', $filePath);
        $filename = $explodeArr[sizeof($explodeArr)-1];
        return (new File($filePath, 'r'))->isTest(true)->setSaveName($filename)->setUploadInfo(['name' => $filename]);
    }

    /**
     * @param $file Object think\File
     * @return bool
     * @throws Exception
     * @throws OssException
     */
    public function upload($file) {
        // LogHelper::logDebug("File class: ".get_class($file));
        if (self::_moveToTempDir($file)) {
            if ($this->storage() == 'file') {
                return true;
            } elseif ($this->storage() == 'oss') {
                $this->_uploadOss();
                $this->delTempFile();
                return true;
            } else {
                // Todo add s3 support
            }
        }
        return false;
    }

    /**
     * @param $file
     * @return bool
     * @throws Exception
     */
    protected function _moveToTempDir($file) {
        $validParams = ['size' => $this->maxFileSize()];
        // LogHelper::logDebug("allowedFileExts: ".$this->allowedFileExts().", maxFileSize: ".$this->maxFileSize());
        if ($this->allowedFileExts() != '*') $validParams['ext'] = $this->allowedFileExts();
        // Todo fix move() unexpected target folder
        // self::$tempFile = $file->validate($validParams)->move($this->localTempDir(), '');  // 适用于 TP5
        self::$tempFile = $file->move($this->localTempDir(), '');

        if (self::$tempFile) {
            LogHelper::logDebug("Success move file to ".$this->localTempDir().", then store as ".$this->storage());
            return true;
        } else {
            throw new Exception($file->getSaveName().$file->getError().", 允许的文件后缀有 ".$this->allowedFileExts()); // 文件不符合要求
            // return false;
        }
    }

    /**
     * @throws Exception
     * @throws \OSS\Core\OssException
     */
    protected function _uploadOss() {
        $remoteDir = ltrim($this->storeDir(), '/'); // oss的目录
        $remoteFilePath = $remoteDir . '/' . $this->filename();

        try {
            $result = OssHelper::ossUpload($remoteDir, $remoteFilePath, $this->tempFilePath());
            return $result['oss-request-url'];
        } catch (OssException $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function delTempFile() {
        if (self::$configs['delTempFile']) {
            unlink($this->tempFilePath());
            if (file_exists($this->tempFilePath())) {
                LogHelper::logDebug("Failed Delete temp file ".$this->filename(), LogHelper::LEVEL_ERROR);
                return false;
            } else {
                LogHelper::logDebug("Deleted temp file ".$this->filename());
                return true;
            }
        } else {
            return false;
        }
    }
}
