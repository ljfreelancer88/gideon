<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

use Throwable;

final class Collect implements CollectInterface
{
    public function __construct(
        private ExcimerWrapper $profiler,
        private WriterInterface $writer
    ) {}

    /**
     * Sets up per-request profiling.
     *
     * Starts the profiler and registers a shutdown function to stop it,
     * obtain the speedscope data, update the profile name, and output via the writer.
     */
    public function perRequestProfiling(): bool
    {
        if (!extension_loaded('excimer')) {
            error_log('Excimer extension not loaded');
            return false;
        }

        try {
            $this->profiler->start();

            register_shutdown_function(function () {
                $this->profiler->stop();
                $data = $this->profiler->getSpeedscopeData();
                $data['profiles'][0]['name'] = $_SERVER['REQUEST_URI'] ?? 'index';
                $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $this->writer->write($json);
            });

            return true;
        } catch (Throwable $e) {
            error_log('Failed to start Excimer profiler: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sets up site-wide profiling in a static context.
     *
     * This method obtains a shared ExcimerWrapper instance using its
     * siteWideInstance() method, attaches a flush callback, and starts profiling.
     *
     * @param WriteInterface $writer  The writer to output profiler data.
     * @param float          $period  Sampling period in seconds.
     * @param int            $maxDepth Maximum stack trace depth.
     * @return bool True if the profiler started successfully.
     */
    public static function siteWideProfiling(
        WriterInterface $writer, 
        float $period = 60, 
        int $maxDepth = 250
    ): bool {
        if (!extension_loaded('excimer')) {
            error_log('Excimer extension not loaded');
            return false;
        }

        // This static variable keeps the profiler instance alive for the duration of the request.
        static $realProf;

        try {
            $realProf = ExcimerWrapper::siteWideInstance($period, $maxDepth);
            $realProf->setFlushCallback(
                static function ($excimer) use ($writer) {
                    $writer->write($excimer->formatCollapsed());
                }, 
                1
            );

            $realProf->start();
            return true;
        } catch (Throwable $e) {
            error_log('Failed to start Excimer profiler: ' . $e->getMessage());
            return false;
        }
    }
}
