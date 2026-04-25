<?php

namespace Hebinet\Tests\Notifications\Stubs;

use Illuminate\Contracts\Config\Repository;

class ConfigStub implements Repository
{
    public function has($key): bool
    {
        return false;
    }

    public function get($key, $default = null)
    {
        return $default;
    }

    public function all(): array
    {
        return [];
    }

    public function set($key, $value = null): void
    {
    }

    public function prepend($key, $value): void
    {
    }

    public function push($key, $value): void
    {
    }
}
