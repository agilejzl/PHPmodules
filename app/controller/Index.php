<?php
namespace app\controller;

use app\BaseController;
use core\helper\LogHelper;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        $modules = ['LogHelper' => '已加载'];
        LogHelper::logDebug($modules);

        View::assign('modules', $modules);
        // 不带任何参数 自动定位当前操作的模板文件
        return View();
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
