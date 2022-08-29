<?php

namespace HumbleCore\Support\Traits;

use Illuminate\Support\Str;

trait HasBuilder
{
    protected $builder;

    protected $exists = false;

    public function __construct($exists = false)
    {
        $this->exists = $exists;

        if (! $exists) {
            $this->initBuilder();
        }
    }

    public function boot(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        $this->fireAppends();
    }

    public function newInstance(array $attributes = [], array $appends = [], array $mutators = [])
    {
        $model = new static(true);

        $model->setAppends($appends);

        $model->setMutators($mutators);

        $model->boot($attributes);

        return $model;
    }

    public static function hydrate(array $items, array $appends, array $mutators)
    {
        $instance = new static;

        $items = array_map(function ($item) use ($instance, $appends, $mutators) {
            return $instance->newInstance((array) $item, $appends, $mutators);
        }, $items);

        return collect($items);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $parameters);
        }

        if (method_exists($this, 'scope'.Str::studly($method))) {
            return $this->{'scope'.Str::studly($method)}($this->builder, ...$parameters);
        }

        if ($this->exists) {
            return;
        }

        return call_user_func_array([$this->builder, $method], $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        return call_user_func_array([$instance, $method], $parameters);
    }
}
