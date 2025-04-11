<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

interface ProfilerInterface
{
    public function start(): void;
    public function stop(): void;
    public function setFlushCallback(callable $callback, int $interval): void;
    public function getSpeedscopeData(): array;
    public function formatCollapsed(): string;
}
