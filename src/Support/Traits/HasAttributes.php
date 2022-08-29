<?php

namespace HumbleCore\Support\Traits;

use Illuminate\Support\Str;

trait HasAttributes
{
    protected $attributes = [];

    protected $appends = [];

    protected $mutators = [];

    public function append($attributes)
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );

        return $this;
    }

    public function setAppends(array $appends)
    {
        $this->appends = $appends;

        return $this;
    }

    public function getAppends()
    {
        return $this->appends;
    }

    public function mutate($attributes)
    {
        $this->mutators = array_unique(
            array_merge($this->mutators, is_string($attributes) ? func_get_args() : $attributes)
        );

        return $this;
    }

    public function setMutators(array $mutators)
    {
        $this->mutators = $mutators;

        return $this;
    }

    public function getMutators()
    {
        return $this->mutators;
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function hasSetMutator($key)
    {
        return in_array($key, $this->mutators) && method_exists($this, 'set'.Str::studly($key).'Attribute');
    }

    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}($value);
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    protected function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    protected function fireAppends()
    {
        foreach ($this->appends as $append) {
            if ($this->hasGetMutator($append)) {
                $value = $this->getAttributeFromArray($append);
                $value = $this->mutateAttribute($append, $value);

                $this->setAttribute($append, $value);
            }
        }
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]) ?? null;
    }

    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}
