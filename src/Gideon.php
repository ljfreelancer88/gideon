<?php

namespace Ljfreelancer88\Gideon;

use ExcimerProfiler;
use Ljfreelancer88\Gideon\GideonInterface;

final class Gideon implements GideonInterface
{
    private static ?ExcimerProfiler $profiler = null;
    private $fileHandle = null;
    private string $outputPath;
    private int $periodSeconds;
    private int $maxDepth;

    /**
     * @param string $outputPath Path to write trace logs
     * @param int $periodSeconds Sampling period in seconds (default 60)
     * @param int $maxDepth Maximum stack trace depth (default 250)
     */
    public function __construct(
        string $outputPath = '/tmp/excimer-traces.log',
        int $periodSeconds = 60,
        int $maxDepth = 250
    ) {
        $this->outputPath = $outputPath;
        $this->periodSeconds = $periodSeconds;
        $this->maxDepth = $maxDepth;        
    }

    public function __destruct()
    {
        if (self::$profiler !== null) {
            self::$profiler->stop();
            self::$profiler = null;
        }

        if ($this->fileHandle) {
            if (fclose($this->fileHandle) === false) {
                error_log('Failed to close profiler output file');
            }
            $this->fileHandle = null;
        }
    }

    /**
     * Sets up per-request profiling.
     *
     * @return bool True if profiler started successfully, false otherwise.
     */
    public function perRequest(): bool
    {
        if (!extension_loaded('excimer')) {
            error_log('Excimer extension not loaded');
            return false;
        }

        if (!$this->openFileHandle()) {
            return false;
        }

        try {
            $excimer = new ExcimerProfiler();
            $excimer->setPeriod( 0.001 ); // 1ms sampling interval
            $excimer->setEventType(EXCIMER_REAL);
            $excimer->start();

            register_shutdown_function(function() use ($excimer) {
                $excimer->stop();
                $data = $excimer->getLog()->getSpeedscopeData();
                $data['profiles'][0]['name'] = $_SERVER['REQUEST_URI'] ?? 'index';
                $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($this->fileHandle) {
                    $bytesWritten = fwrite($this->fileHandle, $json);                    
                    if ($bytesWritten === false) {
                        error_log('Failed to write profiler data to file');
                    }
                    fflush($this->fileHandle);
                }
            });

            return true;
        } catch (Throwable $e) {
            error_log('Failed to start Excimer profiler: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sets up site-wide sampling profiling.
     *
     * @return bool True if profiler started successfully, false otherwise.
     */
    public function siteWide(): bool 
    {
        if (!extension_loaded('excimer')) {
            error_log('Excimer extension not loaded');
            return false;
        }

        if (!$this->openFileHandle()) {
            return false;
        }

        try {
            self::$profiler = new ExcimerProfiler();
            self::$profiler->setEventType(EXCIMER_REAL);
            self::$profiler->setPeriod($this->periodSeconds);
            self::$profiler->setMaxDepth($this->maxDepth);

            // In production, assuming requests generally take far less than 60s,
            // most web requests will never call this, and some will call it once.
            self::$profiler->setFlushCallback(function($excimer) {
                // TODO: Create a Redis/file log writer and call it whichever you'd like to use
                // Use this for now
                if ($this->fileHandle) {
                    $bytesWritten = fwrite(
                        $this->fileHandle, 
                        $excimer->formatCollapsed()
                    );
                    
                    if ($bytesWritten === false) {
                        error_log('Failed to write profiler data to file');
                    }
                    fflush($this->fileHandle);
                }
            }, 1);

            self::$profiler->start();
            return true;
        } catch (Throwable $e) {
            error_log('Failed to start Excimer profiler: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Opens the file handle if not already open.
     *
     * @return bool True if file handle is open, false otherwise.
     */
    private function openFileHandle(): bool
    {
        if (!$this->fileHandle) {
            $this->fileHandle = fopen($this->outputPath, 'a');
            if ($this->fileHandle === false) {
                error_log('Failed to open profiler output file: ' . $this->outputPath);
                return false;
            }
        }
        return true;
    }
}
