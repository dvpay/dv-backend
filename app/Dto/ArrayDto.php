<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Support\Str;
use ReflectionClass;

class ArrayDto
{
    public function __construct(array $args = [])
    {
        $reflection = new ReflectionClass(static::class);
        $props = $reflection->getProperties();
        foreach ($props as $prop) {
            if (isset($args[$prop->getName()])) {
                $prop->setValue($this, $args[$prop->getName()]);
            }
        }
    }

    public function toSnakeCase(): array
    {
        $result = [];

        foreach ($this as $key => $value) {
            $snakeCase = Str::snake((string)$key);
            $result[$snakeCase] = $value;
        }

        return $result;
    }
}