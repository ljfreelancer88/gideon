<?php

namespace Ljfreelancer88\Gideon;

use ExcimerProfiler;
use Ljfreelancer88\Gideon\GideonInterface;

final class Gideon implements GideonInterface
{
    private static ?ExcimerProfiler $profiler = null;
    private string $outputPath;
    private int $periodSeconds;
    private int $maxDepth;
    private $fileHandle = null;

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

    public function start(): bool 
    {
        if (!extension_loaded('excimer')) {
            error_log('Excimer extension not loaded');
            return false;
        }

        if (!$this->fileHandle) {
            $this->fileHandle = fopen($this->outputPath, 'a');
            if ($this->fileHandle === false) {
                error_log('Failed to open profiler output file: ' . $this->outputPath);
                return false;
            }
        }

        try {
            self::$profiler = new ExcimerProfiler();
            self::$profiler->setEventType(EXCIMER_REAL);
            self::$profiler->setPeriod($this->periodSeconds);
            self::$profiler->setMaxDepth($this->maxDepth);

            self::$profiler->setFlushCallback(function($log) {
                // TODO: Create a Redis/file log writer and call it whichever you'd like to use
                // Use this for now
                if ($this->fileHandle) {
                    $bytesWritten = fwrite(
                        $this->fileHandle, 
                        $log->formatCollapsed()
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
}
