<?php

namespace Aoux\SystemMonitor\Services;

class SystemResourceMonitor
{
    protected $config;

    public function __construct()
    {
        $this->config = config('system-monitor.system', []);
    }

    public function getStatus()
    {
        return [
            'cpu' => $this->getCpuStatus(),
            'memory' => $this->getMemoryStatus(),
            'disk' => $this->getDiskStatus(),
            'php' => $this->getPhpStatus(),
        ];
    }

    protected function getCpuStatus()
    {
        if (!$this->config['show_cpu']) {
            return null;
        }

        if (PHP_OS === 'WINNT') {
            $load = $this->getWindowsLoad();
        } else {
            $load = \sys_getloadavg();
        }

        $cores = $this->getCpuCores();

        return [
            'load' => [
                '1min' => $load[0] ?? 0,
                '5min' => $load[1] ?? 0,
                '15min' => $load[2] ?? 0,
            ],
            'cores' => $cores,
            'usage' => $this->getCpuUsage(),
        ];
    }

    protected function getWindowsLoad()
    {
        $load = [0, 0, 0];
        
        if (class_exists('COM')) {
            try {
                $wmi = new \COM('Winmgmts://');
                $cpus = $wmi->InstancesOf('Win32_Processor');
                
                $totalLoad = 0;
                $cpuCount = 0;
                
                foreach ($cpus as $cpu) {
                    $totalLoad += $cpu->LoadPercentage;
                    $cpuCount++;
                }
                
                if ($cpuCount > 0) {
                    $avgLoad = $totalLoad / $cpuCount;
                    $load = [$avgLoad, $avgLoad, $avgLoad];
                }
            } catch (\Exception $e) {
                // COM sınıfı kullanılamıyorsa varsayılan değerleri kullan
            }
        }
        
        return $load;
    }

    protected function getMemoryStatus()
    {
        if (!$this->config['show_memory']) {
            return null;
        }

        $total = $this->getTotalMemory();
        $free = $this->getFreeMemory();
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    protected function getDiskStatus()
    {
        if (!$this->config['show_disk']) {
            return null;
        }

        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    protected function getPhpStatus()
    {
        if (!$this->config['show_php']) {
            return null;
        }

        return [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    protected function getCpuCores()
    {
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            return count($matches[0]);
        }

        if (PHP_OS === 'WINNT') {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');
            if ($process !== false) {
                fgets($process);
                $cores = (int) fgets($process);
                pclose($process);
                return $cores;
            }
        }

        return 1;
    }

    protected function getCpuUsage()
    {
        if (PHP_OS === 'WINNT') {
            $cmd = 'wmic cpu get loadpercentage';
            exec($cmd, $output);
            if (isset($output[1])) {
                return (int) $output[1];
            }
        } else {
            $load = \sys_getloadavg();
            return $load[0] * 100;
        }

        return 0;
    }

    protected function getTotalMemory()
    {
        if (PHP_OS === 'WINNT') {
            $cmd = 'wmic ComputerSystem get TotalPhysicalMemory';
            exec($cmd, $output);
            if (isset($output[1])) {
                return (int) $output[1];
            }
        } else {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $matches);
            if (isset($matches[1])) {
                return (int) $matches[1] * 1024;
            }
        }

        return 0;
    }

    protected function getFreeMemory()
    {
        if (PHP_OS === 'WINNT') {
            $cmd = 'wmic OS get FreePhysicalMemory';
            exec($cmd, $output);
            if (isset($output[1])) {
                return (int) $output[1] * 1024;
            }
        } else {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemFree:\s+(\d+)/', $meminfo, $matches);
            if (isset($matches[1])) {
                return (int) $matches[1] * 1024;
            }
        }

        return 0;
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 