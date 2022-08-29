<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Jsonable;
use HumbleCore\Support\Traits\HasAttributes;
use HumbleCore\Support\Traits\HasBuilder;
use Illuminate\Support\Traits\Conditionable;

class TermModel extends Jsonable
{
    use Conditionable;
    use HasAttributes;
    use HasBuilder;

    public function initBuilder()
    {
        $this->builder = new TermBuilder($this);
        $this->builder->name($this->taxonomy);
    }
}
