<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Jsonable;
use HumbleCore\Support\Traits\HasAttributes;
use HumbleCore\Support\Traits\HasBuilder;
use Illuminate\Support\Traits\Conditionable;

class PostModel extends Jsonable
{
    use Conditionable;
    use HasAttributes;
    use HasBuilder;

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
