<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run against anything that is not a dedicated test database.
     * RefreshDatabase drops and rebuilds every table, so pointing tests at the live
     * database would destroy real registry data.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $database = (string) $app['config']->get('database.connections.'.$app['config']->get('database.default').'.database');

        if (! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Refusing to run tests against database [{$database}]. Use a database whose name ends in _test (see phpunit.xml)."
            );
        }

        return $app;
    }

    /**
     * Sends the next request as the owner of this token. Laravel keeps the user resolved by an
     * earlier request on the guard for the rest of the test, so the guard is cleared first; without
     * this a second request in the same test would still act as the first account.
     */
    protected function apiAs(string $plainTextToken): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($plainTextToken);
    }
}
