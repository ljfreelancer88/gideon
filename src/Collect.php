<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

use Throwable;

final class Collect
{
    public function __construct(
        private ExcimerWrapper $profiler,
        private WriterInterface $writer
    ) {}

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

    public function siteWideProfiling(): bool
    {
        if (!extension_loaded('excimer')) {
            error_log('Excimer extension not loaded');
            return false;
        }

        try {
            $this->profiler->setFlushCallback(function ($excimer) {
                $this->writer->write($excimer->formatCollapsed());
            }, 1);

            $this->profiler->start();
            return true;
        } catch (Throwable $e) {
            error_log('Failed to start Excimer profiler: ' . $e->getMessage());
            return false;
        }
    }
}
