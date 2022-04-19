<?php
namespace app\web\controller;

use app\web\BaseController;
use core\helper\LogHelper;
use core\uploader\BaseUploader;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        $modules = ['LogHelper' => '/', 'TimerHelper' => '定时任务', 'SysConfHelper' => '/admin',
            'FileUploader' => '/web/index/uploader'];
        LogHelper::logDebug($modules);

        View::assign('modules', $modules);
        // 不带任何参数 自动定位当前操作的模板文件
        return View();
    }

    public function uploader() {
        return View();
    }

    public function do_upload() {
        $avatarUploadConf = [
            'storage' => 'file',
            'allowedExts' => 'png,jpg',
            'storeDir' => '/avatars',
            'delTempFile' => true,
            'maxSize' => 1024 * 1024 * 1024
        ];
        $file = request()->file('avatar');
        dump($file);
        $uploader = new BaseUploader($avatarUploadConf);
        $uploader->upload($file);
    }
}
