<?php
namespace core\helper;

// 使用示范如下：
// (new CpuTester(2000000, true))->runScore();
class CpuTester {
    private $debugPrint = false;
    private $maxNum;
    private $minNum;
    private $currMinNum;
    private $groupSum = 0;
    private $donePercent = 0;
    private $groupSize;
    private $startTime;

    public function totalNums() {
        return $this->maxNum - $this->minNum;
    }

    public function __construct($maxNum, $debug = false) {
        $this->debugPrint = $debug;
        $this->maxNum = $maxNum;
        $this->minNum = -$this->maxNum;
        $groupCount = 100;
        if ($groupCount > $this->maxNum) {
            $groupCount = $this->maxNum;
        }

        if ($this->minNum >= -$this->maxNum && $this->maxNum >= $this->minNum) {
            $this->groupSize = floor($this->totalNums() / $groupCount);
            $this->debug("求和从 $this->maxNum 到 $this->minNum ，分组为 $groupCount 组，每 $this->groupSize 个为一组\n");
        } else {
            throw new Exception('求和数字必须指定大于0');
        }
    }

    public function doCount($num, $currSum = null) {
        if (empty($currSum)) {
            $currSum = $num;
        } else {
            $currSum = $currSum + $num;
        }
        // $this->debug(",$num th");
        if ($num > $this->currMinNum) {
            return $this->doCount($num-1, $currSum);
        } else {
            return $currSum;
        }
    }

    public function doCountByGroup($num, $lastPercent = 0) {
        $nextGroupNum = $num - $this->groupSize ;
        if ($nextGroupNum >= $this->minNum) {
            $this->currMinNum = $nextGroupNum + 1;
            $this->groupSum = $this->doCount($num, $this->groupSum);
            $this->donePercent = round(($this->maxNum - $this->currMinNum)/($this->totalNums()*1.0)*100,1);

            if ($this->donePercent == 0 || $this->donePercent >= $lastPercent + 5) {
                $lastPercent = $this->donePercent;
                $timer = $this->showTime();
                $this->debug("\n $timer 秒完成 $this->donePercent%, 本组 ($nextGroupNum, $num], 已求和值 $this->groupSum ");
            } else {
                $this->debug(".");
            }
            return $this->doCountByGroup($nextGroupNum, $lastPercent);
        } else {
            $this->currMinNum = $this->minNum;
            $this->groupSum = $this->doCount($num, $this->groupSum);
            $timer = $this->showTime();
            $this->debug("\n $timer 秒完成 100%，最后一组 [$this->minNum, $num], 最终求和值 $this->groupSum");
            return $this->groupSum;
        }
    }

    public function showTime($precision = 3) {
        $startTime = $this->startTime;
        $endTime = microtime(true);
        return round($endTime - $startTime, $precision);
    }

    public function debug($msg) {
        if ($this->debugPrint) {
            print $msg;
        }
    }

    public function runScore() {
        $this->startTime = microtime(true);
        $num = $this->maxNum;
        $sum = $this->doCountByGroup($num);

        $timer = $this->showTime(6);
        $this->debug("\n递归求和计算 $num ，求和结果 $sum, 总耗时 $timer 秒\n");
        $score = round($this->totalNums() / (10000*$timer), 1);
        $this->debug("\n---- 你的CPU单进程评分 $score 分 ----\n");
        return $score;
    }
}

