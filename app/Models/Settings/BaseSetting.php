<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

abstract class BaseSetting
{
    /**
     * @param array $data
     * @return mixed
     */
    abstract public function fromArray (array $data);

    /**
     * @return array
     */
    abstract public function toArray (): array;

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return $this
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): static
    {
        $instance = new static;
        $instance->fromArray(json_decode($value, true) ?? []);

        return $instance;
    }

    /**
     * @param Model $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return array|null[]
     * @throws \Exception
     */
    public function set(Model $model, string $key, $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if (is_array($value)) {
            $model = new static();
            $model->fromArray($value);
            $value = $model;
        } elseif (!$value instanceof static) {
            throw new \Exception('The given value is not an ' . static::class . ' instance.');
        }

        return [$key => $value->__toString()];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}