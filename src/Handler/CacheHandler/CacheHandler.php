<?php

namespace App\Handler\CacheHandler;

class CacheHandler
{
    public function clear(string $tag): void
    {
    }

    public function get(string $namespace, string $key, callable $callback): mixed
    {
        return null;
    }
}
