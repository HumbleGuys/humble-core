<?php

namespace HumbleCore\Support;

use JsonSerializable;

abstract class Jsonable implements JsonSerializable
{
    abstract protected function toArray();

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
