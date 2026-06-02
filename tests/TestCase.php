<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Sanctum caches the resolved user in the guard instance for the lifetime
    // of the application singleton. Between HTTP calls in the same test we must
    // flush those guards so each request authenticates fresh from the DB.
    protected function resetAuthGuards(): void
    {
        $this->app->make('auth')->forgetGuards();
    }
}
