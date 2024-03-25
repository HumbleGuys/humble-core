<?php

namespace HumbleCore\Support\Traits;

use Illuminate\Database\Eloquent\MissingAttributeException;

trait HasIlluminateAttributes
{
    public $wasRecentlyCreated = false;

    public function getIncrementing(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return $this->attributesToArray();
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
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

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     */
    public function offsetExists($offset): bool
    {
        try {
            return ! is_null($this->getAttribute($offset));
        } catch (MissingAttributeException) {
            return false;
        }
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     */
    public function __isset($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     */
    public function __unset($key): void
    {
        $this->offsetUnset($key);
    }
}
