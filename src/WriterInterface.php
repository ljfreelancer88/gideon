<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

interface WriterInterface
{
    public function write(string $data): void;
}
