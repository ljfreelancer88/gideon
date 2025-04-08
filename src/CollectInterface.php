<?php

namespace Ljfreelancer88\Gideon;

interface CollectInterface {
    public function perRequest(): bool;
    public function siteWide(): bool;
}