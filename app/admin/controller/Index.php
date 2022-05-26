<?php
namespace app\admin\controller;

use app\admin\BaseController;
use core\helper\CpuTester;
use core\helper\SysConfHelper;
use think\facade\View;

class Index extends BaseController {
    public function index()
    {
        $CPUScore =  (new CpuTester(2000000))->runScore();
        View::assign('CPUScore', $CPUScore);
        View::assign('env', SysConfHelper::currEnv());
        View::assign('web_front_host', SysConfHelper::getSysConf('web_front_host'));
        View::assign('default_timezone', SysConfHelper::getEnvConf('app.default_timezone'));
        View::assign('database', SysConfHelper::getEnvConf('database.database'));

        $branch = shell_exec('git branch --no-color 2> /dev/null | sed -e \'/^[^*]/d\'');
        $lastGit = shell_exec('git log --name-status HEAD^..HEAD;');
        $lastGit = str_replace('Date:', '<br/>Date:', $lastGit);
        $lastGit = str_replace('M	', '<br/>M	', $lastGit);
        View::assign('lastGit', '(Branch '.$branch.') '.$lastGit);
        return View();
    }
}
