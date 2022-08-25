<?php

namespace HumbleCore\PostTypes;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\ForwardsCalls;

class PostModel implements Jsonable
{
    use Conditionable;
    use HasAttributes;
    use ForwardsCalls;

    public string $name;

    protected $builder;

    protected $exists = false;

    public function __construct($exists = false)
    {
        $this->exists = $exists;

        if (! $exists) {
            $this->initBuilder();
        }
    }

    public function initBuilder()
    {
        $this->builder = new PostBuilder($this);
        $this->builder->postType($this->name);
    }

    public function boot(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        //$this->fireAppends();
    }

    public function newInstance(array $attributes = [])
    {
        $model = new static(true);

        //$model->setAppends($appends);

        //$model->setMutators($mutators);

        $model->boot($attributes);

        return $model;
    }

    public static function hydrate(array $items)
    {
        $instance = new static;

        $items = array_map(function ($item) use ($instance) {
            return $instance->newInstance((array) $item);
        }, $items);

        return collect($items);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    public function usesTimestamps()
    {
        return false;
    }

    public function getIncrementing()
    {

    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->builder, $method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->escapeWhenCastingToString
                    ? e($this->toJson())
                    : $this->toJson();
    }
}
