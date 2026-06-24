<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class TestCacheController extends Controller
{
    /**
     * Chẩn đoán trạng thái hoạt động của hệ thống Cache
     * Lệnh chạy: php yii test-cache
     */
    public function actionIndex()
    {
        $this->stdout("--- CACHE SYSTEM DIAGNOSIS ---\n", Console::FG_CYAN, Console::BOLD);

        $cache = Yii::$app->cache;
        if (!$cache) {
            $this->stderr("Error: Cache component is not configured!\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $driverClass = get_class($cache);
        $driverName = basename(str_replace('\\', '/', $driverClass));

        $this->stdout("Driver: ", Console::FG_YELLOW);
        $this->stdout("$driverName\n", Console::FG_GREEN, Console::BOLD);

        // 1. Kiểm tra kết nối / phân quyền ghi
        $this->stdout("Connection: ", Console::FG_YELLOW);
        if ($cache instanceof \yii\redis\Cache) {
            try {
                $redis = $cache->redis;
                if (is_string($redis)) {
                    $redis = Yii::$app->get($redis);
                }

                if ($redis instanceof \yii\redis\Connection) {
                    $redis->open();
                    $ping = $redis->executeCommand('PING');
                    if ($ping === 'PONG' || $ping === true || $ping === 1 || $ping === '1') {
                        $pingValue = ($ping === true) ? 'true' : $ping;
                        $this->stdout("OK (Redis Ping: $pingValue)\n", Console::FG_GREEN, Console::BOLD);
                    } else {
                        $this->stderr("FAILED (Unexpected response: $ping)\n", Console::FG_RED);
                    }
                } else {
                    $this->stderr("FAILED (Connection component invalid)\n", Console::FG_RED);
                }
            } catch (\Exception $e) {
                $this->stderr("FAILED (Error: " . $e->getMessage() . ")\n", Console::FG_RED);
            }
        } else {
           
            $runtimePath = Yii::getAlias('@runtime/cache');
            if (is_writable(Yii::getAlias('@runtime')) || (is_dir($runtimePath) && is_writable($runtimePath))) {
                $this->stdout("OK (Runtime Directory is Writable)\n", Console::FG_GREEN, Console::BOLD);
            } else {
                $this->stderr("FAILED (Runtime Directory is NOT Writable)\n", Console::FG_RED);
            }
        }

        // Test Write / Read / Delete
        $testKey = 'test_cli_cache_key';
        $testValue = ['status' => 'OK', 'timestamp' => time()];

        // 2. Đo thời gian ghi cache
        $startWrite = microtime(true);
        $writeOk = $cache->set($testKey, $testValue, 60);
        $endWrite = microtime(true);
        $writeTime = ($endWrite - $startWrite) * 1000; // chuyển sang ms

        $this->stdout("Write: ", Console::FG_YELLOW);
        if ($writeOk) {
            $this->stdout("OK\n", Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr("FAILED\n", Console::FG_RED);
        }

        // 3. Đo thời gian đọc cache
        $startRead = microtime(true);
        $readValue = $cache->get($testKey);
        $endRead = microtime(true);
        $readTime = ($endRead - $startRead) * 1000; // ms

        $this->stdout("Read: ", Console::FG_YELLOW);
        if ($readValue !== false && isset($readValue['status']) && $readValue['status'] === 'OK') {
            $this->stdout("OK\n", Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr("FAILED\n", Console::FG_RED);
        }

        // 4. Kiểm tra xóa cache
        $deleteOk = $cache->delete($testKey);
        $this->stdout("Delete: ", Console::FG_YELLOW);
        if ($deleteOk) {
            $this->stdout("OK\n", Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr("FAILED\n", Console::FG_RED);
        }

        // 5. Tổng thời gian thực thi (Đọc + Ghi)
        $totalTime = $writeTime + $readTime;
        $this->stdout("Execution Time (Write+Read): ", Console::FG_YELLOW);
        $this->stdout(number_format($totalTime, 2) . "ms\n", Console::FG_GREEN, Console::BOLD);

        return ExitCode::OK;
    }
}
