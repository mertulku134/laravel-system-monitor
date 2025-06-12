<?php

namespace Aoux\SystemMonitor\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class LogMonitor
{
    protected $logPath;
    protected $maxLines;
    protected $logLevel;

    public function __construct()
    {
        $this->logPath = storage_path('logs/' . config('system-monitor.logging.file', 'laravel.log'));
        $this->maxLines = config('system-monitor.logging.max_lines', 1000);
        $this->logLevel = config('system-monitor.logging.level', 'error');
    }

    public function getLogs()
    {
        if (!File::exists($this->logPath)) {
            return [];
        }

        $logs = [];
        $lines = File::lines($this->logPath);
        $count = 0;

        foreach ($lines as $line) {
            if ($count >= $this->maxLines) {
                break;
            }

            if ($this->isValidLogLevel($line)) {
                $logs[] = $this->parseLogLine($line);
                $count++;
            }
        }

        return array_reverse($logs);
    }

    protected function isValidLogLevel($line)
    {
        $levels = [
            'emergency' => 'emergency',
            'alert' => 'alert',
            'critical' => 'critical',
            'error' => 'error',
            'warning' => 'warning',
            'notice' => 'notice',
            'info' => 'info',
            'debug' => 'debug'
        ];

        $currentLevel = array_search($this->logLevel, $levels);
        $lineLevel = strtolower($line);

        foreach ($levels as $level => $value) {
            if (strpos($lineLevel, $value) !== false) {
                return array_search($level, $levels) <= $currentLevel;
            }
        }

        return false;
    }

    protected function parseLogLine($line)
    {
        $pattern = '/^\[(.*?)\]\s+(\w+)\.(\w+):\s+(.*?)$/';
        if (preg_match($pattern, $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'environment' => $matches[2],
                'level' => $matches[3],
                'message' => $matches[4]
            ];
        }

        return [
            'timestamp' => now(),
            'environment' => 'local',
            'level' => 'info',
            'message' => $line
        ];
    }

    public function clearLogs()
    {
        if (File::exists($this->logPath)) {
            File::put($this->logPath, '');
            return true;
        }
        return false;
    }
} 