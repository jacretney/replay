<?php

namespace Tests;

use Kayrunm\Replay\ReplayServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ReplayServiceProvider::class];
    }
}
