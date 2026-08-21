<?php

namespace HumbleCore\PostTypes;

use ArrayAccess;
use HumbleCore\Support\Jsonable;
use HumbleCore\Support\Traits\HasBuilder;
use HumbleCore\Support\Traits\HasIlluminateAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Support\Traits\Conditionable;

/**
 * @property int $id
 *
 * @method static static exclude(mixed $ids)
 * @method static static find(mixed $ids)
 * @method static static name(string $name)
 * @method static static offset(int $offset)
 * @method static static orderByRandom()
 * @method static static orderBySortOrder(string $order = 'asc')
 * @method static static orderByTitle(string $order = 'asc')
 * @method static static search(string $query)
 * @method static static status(mixed $status)
 * @method static static take(int $take)
 * @method static static where(mixed $field, mixed $operator = null, mixed $value = null, mixed $type = null, mixed $relation = null)
 * @method static static whereDate(mixed $field, mixed $operator, mixed $value, mixed $relation = null)
 * @method static static whereHasTerm(\HumbleCore\Taxonomies\TermModel $term)
 * @method static static whereInTerms(\Illuminate\Support\Collection $terms, string $relation = 'OR')
 * @method static static withAcf(mixed $fields = true)
 * @method static static withDate()
 * @method static static withPermalink()
 * @method static static withTitle()
 * @method static \Illuminate\Support\Collection<int, static> get()
 * @method static static|null first()
 * @method static array paginate(int $perPage = 20)
 *
 * @mixin PostBuilder<static>
 */
class PostModel extends Jsonable implements ArrayAccess
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
