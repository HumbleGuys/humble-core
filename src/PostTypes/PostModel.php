<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Jsonable;
use HumbleCore\Support\Traits\HasBuilder;
use HumbleCore\Support\Traits\HasIlluminateAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Support\Traits\Conditionable;

class PostModel extends Jsonable
{
    use Conditionable;
    use HasAttributes;
    use HasBuilder;
    use HasIlluminateAttributes;
    use HidesAttributes;

    public function initBuilder()
    {
        $this->builder = new PostBuilder($this);
        $this->builder->postType($this->postType);
    }

    public function hasStatus(string $status)
    {
        return $this->getStatus() === $status;
    }

    public function getStatus()
    {
        return get_post_status($this->id);
    }
}
