<?php

namespace Ljfreelancer88\Gideon;

interface ProfilerInterface
{
    public function start(): void;
    public function stop(): void;
    public function getData(): array;
}
