<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Jsonable;
use HumbleCore\Support\Traits\HasBuilder;
use HumbleCore\Support\Traits\HasIlluminateAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Support\Traits\Conditionable;

class TermModel extends Jsonable
{
    use Conditionable;
    use HasAttributes;
    use HasBuilder;
    use HasIlluminateAttributes;
    use HidesAttributes;

    public function initBuilder()
    {
        $this->builder = new TermBuilder($this);
        $this->builder->name($this->taxonomy);
    }
}
