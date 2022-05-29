PHPmodules
===============

## 项目环境安装
~~~
composer install
~~~

## 主要模块目录 extend/core 和 app/worker

* LogHelper
业务日志记录模块，支持以下主要功能  
  1. 区分业务类型，分别记录到不同的日志文件，方便后续追踪问题状况  
  2. 可配置最低日志级别，低于该级别不会写入到日志，默认的 LOG_LEVEL 为 INFO级别  
  3. 可记录异常的详细信息，代码行、堆栈信息，使用示范   
```php

        $errInfo = LogHelper::detailError($e, true); 
        LogHelper::logDebug($errInfo, LogHelper::LEVEL_ERROR); 
``` 

* SysConfHelper
  获取系统配置参数  
  1. getSysConf支持获取 config目录下所有配置文件，可返回单个 或者 数组节点  
  2. getEnvConf支持获取 .env文件的单个配置参数  
  使用示范  
```php

        SysConfHelper::currEnv();  
        SysConfHelper::getEnvConf('oss')
```   

* 定时任务&Redis消息队列   
  基于 Workerman 实现 TimerWorker、SubscribeWorker (Redis存储)，同时封装了 TimeHelper 等等。   
  Worker 启动命令： ```php think worker:server```, 然后可通过日志 runtime/log/spider.log 查看任务执行记录
  1. 判断当前时间是否在指定的小时、分钟、秒钟内，可用于精确定时任务的时间，使用示范在 app\worker\TimerWorker
  2. 支持抢购业务逻辑，抢购时间开启的误差在1秒内，示范如下  
 ```php

        $timeStr = $grabTime->format('H:i:s');
        $timeArr = explode(':', $timeStr);
        if (TimeHelper::isInMinutes($timeArr[0], $timeArr[1], $timeArr[1]+1, false)
            && TimeHelper::isInSeconds($timeArr[2], $timeArr[2]+1)) {
            LogHelper::logSpider("Timer4: 现在是 ".$timeStr."，开启抢购!");
        }
```
* OssHelper
阿里云OSS操作工具
  1. 提供了单一客户端实例模式
  2. 封装了常用方法，比如文件上传、文件签名、文件下载
使用示范   
```php

        OssHelper::ossUpload($remoteDir, $remoteFilePath, $this->tempFilePath()); 
```

* BaseUploader
文件上传工具
  1.  可分别为各个上传指定各项参数
  2. 上传后可存储为文件、OSS、AWS S3等等，具体由对应的云存储组件，比如 OssHelper 提供支持
 ```php

        $avatarUploadConf = [
            'storage' => 'file',
            'allowedExts' => 'png,jpg',
            'storeDir' => '/avatars',
            'delTempFile' => true,
            'maxSize' => 1024 * 1024 * 1024
        ];
        $file = request()->file('avatar');
        $uploader = new BaseUploader($avatarUploadConf);
        $uploader->upload($file);
```
