<?php
/**
 * Bitmap 类，基于GMP实现，使用尽可能少的内存，内存占用远优于以下数组的存放方式
 * ini_set('memory_limit','4G');
 * $arr = [];
 * for ($i = 0; $i < 64 * 1000000; $i++) { $arr[] = PHP_INT_MAX; } // 需要占用小2G内存
 */
namespace core\helper;

class Bitmap {
    private $debug = true;
    private $gmp;

    // toStr() 仅仅用于调试，内存消耗大
    public function toStr($base = 10): string {
        return $base.'进制 '.gmp_strval($this->gmp, $base).', 2进制 '.gmp_strval($this->gmp, 2).PHP_EOL;
    }

    public function __construct($initNum = 0) {
        $this->gmp = gmp_init($initNum);
        if ($this->debug) print "Bitmap GMP 初始化为 $initNum".PHP_EOL;
    }

    // 第n位是否为 1，下标从 0 开始
    public function isSet($n): bool {
        $result = gmp_testbit($this->gmp, $n);
        if ($this->debug) printf("Get 第$n 位是 %d,".PHP_EOL, $result);
        return $result;
    }

    // 第 start 到 end 位是否全为1（不包括end）
    public function isSetRange($start, $end): bool {
        $found0 = gmp_scan0($this->gmp, $start);
        $noZero = ($found0 == -1 || $found0 >= $end);
        if ($this->debug && $end != $start+1) {
            printf(__FUNCTION__." Get 第($start, $end] 位是否全1：%d".PHP_EOL, $noZero);
        }
        return $noZero;
    }

    // 设置第n位为 1
    public function set(int $n) {
        gmp_setbit($this->gmp, $n, true);
        if ($this->debug) print "Set 第$n 位为 1".PHP_EOL;
    }

    // 设置第n位为 0
    public function unset(int $n) {
        // gmp_setbit($this->gmp, $n, false);
        gmp_clrbit($this->gmp, $n);
        if ($this->debug) print "Set 第$n 位清零".PHP_EOL;
    }

    // 设置第 start 到 end 位为 0（不包括end）
    public function unsetRange($start, $end) {
        // print __FUNCTION__." begin...".PHP_EOL;
        $num = $start;
        while ($num < $end) {
            $found1 = gmp_scan1($this->gmp, $num);
            if ($found1 >= $start && $found1 < $end) {
                // if ($this->debug) print __FUNCTION__." 第$found1 位待清零. ";
                $this->unset($num);
                $num = $found1 + 1;
            } else {
                // if ($this->debug)  print __FUNCTION__." ($start, $end]区间内没有1.".PHP_EOL;
                break;
            }
        }
        if ($this->debug) print __FUNCTION__." Set 第($start, $end]位全清零".PHP_EOL;
    }
}

function test() {
    $index = 64 * 1000000; // 100万个整数存储3600万位
    $bitmap = new Bitmap(8); // 假如传 8，即二进制的 1000 初始化
    $bitmap->isSet($index);
    $bitmap->set($index);
    $bitmap->isSet($index);
    print PHP_EOL;

    $lastIndex = $index - 2;
    $bitmap->isSet($lastIndex);
    $bitmap->set($lastIndex);
    $bitmap->isSet($lastIndex);
    $bitmap->isSetRange($lastIndex, $index);
    print PHP_EOL;

    $bitmap->isSet($lastIndex+1);
    $bitmap->set($lastIndex+1);
    $bitmap->isSetRange($lastIndex, $index);
    print PHP_EOL;

    $bitmap->unsetRange($lastIndex, $index);
    $bitmap->isSet($lastIndex);
    $bitmap->isSet($lastIndex+1);
    print PHP_EOL;

    $bitmap->isSet($index);
    $bitmap->unset($index);
    $bitmap->isSet($index);
    print $bitmap->toStr();
}
test();

