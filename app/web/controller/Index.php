<?php
namespace app\web\controller;

use app\web\BaseController;
use core\helper\LogHelper;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        $modules = ['LogHelper' => '/', 'TimerHelper' => '定时任务', 'SysConfHelper' => '/admin'];
        LogHelper::logDebug($modules);

        View::assign('modules', $modules);
        // 不带任何参数 自动定位当前操作的模板文件
        return View();
    }
}
