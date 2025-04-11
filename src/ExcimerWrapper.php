<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

use ExcimerProfiler;

final class ExcimerWrapper implements ProfilerInterface
{
    private static ?self $instance = null;
    private ExcimerProfiler $profiler;

    public function __construct(float $periodSeconds = 60, int $maxDepth = 250)
    {
        $this->profiler = new ExcimerProfiler();
        $this->profiler->setPeriod($periodSeconds);
        $this->profiler->setMaxDepth($maxDepth);
        $this->profiler->setEventType(EXCIMER_REAL);
    }

    public function start(): void
    {
        $this->profiler->start();
    }

    public function stop(): void
    {
        $this->profiler->stop();
    }

    public function getSpeedscopeData(): array
    {
        return $this->profiler->getLog()->getSpeedscopeData();
    }

    public function formatCollapsed(): string
    {
        return $this->profiler->formatCollapsed();
    }

    public function setFlushCallback(callable $callback, int $intervalSeconds): void
    {
        $this->profiler->setFlushCallback($callback, $intervalSeconds);
    }

    public static function siteWideInstance(float $periodSeconds = 60, int $maxDepth = 250): self
    {
        if (self::$instance === null) {
            self::$instance = new self($periodSeconds, $maxDepth);
        }
        return self::$instance;
    }
}
