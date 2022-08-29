<?php

namespace HumbleCore\ACF;

use JsonSerializable;

class FlexibleContentRow implements JsonSerializable
{
    public $name;

    public $attributes = [];

    public function __construct($attributes)
    {
        $this->name = $attributes['acf_fc_layout'];

        foreach ($attributes as $key => $value) {
            if ($key !== 'acf_fc_layout') {
                $this->setAttribute($key, $value);
            }
        }
    }

    public function is($name)
    {
        return $this->name === $name;
    }

    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    public function toArray()
    {
        $attributes = $this->attributes;
        $attributes['acf_fc_layout'] = $this->name;

        return $attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
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
