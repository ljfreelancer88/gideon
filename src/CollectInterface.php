<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

interface CollectInterface 
{
    public function perRequestProfiling(): bool;

    public static function siteWideProfiling(WriterInterface $writer, float $period = 60, int $maxDepth = 250): bool;
}