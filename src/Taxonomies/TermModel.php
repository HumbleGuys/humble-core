<?php

namespace HumbleCore\Taxonomies;

use ArrayAccess;
use HumbleCore\Support\Jsonable;
use HumbleCore\Support\Traits\HasBuilder;
use HumbleCore\Support\Traits\HasIlluminateAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Support\Traits\Conditionable;

/**
 * @property int $id
 * @property string $taxonomy
 */
class TermModel extends Jsonable implements ArrayAccess
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
