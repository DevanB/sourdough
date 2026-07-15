<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Browser tests need built Vite assets so the client can hydrate/render.
        if (! $this->runningBrowserTest()) {
            $this->withoutVite();
        }

        config(['inertia.ssr.enabled' => false]);
    }

    private function runningBrowserTest(): bool
    {
        return str_contains(static::class, '\\Browser\\');
    }
}
