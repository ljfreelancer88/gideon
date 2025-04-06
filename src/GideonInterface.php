<?php

namespace Ljfreelancer88\Gideon;

interface GideonInterface {
    public function perRequest(): bool;
    public function siteWide(): bool;
    private function openFileHandle(): bool;
}