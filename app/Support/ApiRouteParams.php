<?php

namespace App\Support;

/**
 * Stand-in for a matched route when a FormRequest is validated outside an HTTP request
 * (the offline sync endpoint runs several operations in one request).
 */
class ApiRouteParams
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(private array $parameters = []) {}

    public function parameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }
}
