<?php

declare(strict_types=1);

use HumbleCore\PostTypes\PostModel;

it('hydrates concrete models and applies Illuminate casts lazily', function (): void {
    $model = AttributeTestPost::hydrate([
        ['id' => 10, 'active' => 1],
    ], [])->first();

    expect($model)->toBeInstanceOf(AttributeTestPost::class)
        ->and($model->id)->toBe(10)
        ->and($model->active)->toBeTrue()
        ->and($model->toArray())->toMatchArray([
            'id' => 10,
            'active' => true,
        ]);
});

it('applies appended accessors when the model is converted to an array', function (): void {
    $model = AttributeTestPost::hydrate([
        ['name' => 'Humble'],
    ], ['label'])->first();

    expect($model->toArray())->toMatchArray([
        'name' => 'Humble',
        'label' => 'HUMBLE',
    ]);
});

it('unsets dynamic attributes without requiring Eloquent relationships', function (): void {
    $model = AttributeTestPost::hydrate([
        ['name' => 'Humble'],
    ], [])->first();

    unset($model->name);

    expect(isset($model->name))->toBeFalse()
        ->and($model->toArray())->not->toHaveKey('name');
});

it('supports array access, assignment, casts, mutators and null existence checks', function (): void {
    $model = new AttributeTestPost(true);

    $model['name'] = '  Humble  ';
    $model['active'] = '0';
    $model['nullable'] = null;

    expect($model['name'])->toBe('Humble')
        ->and($model['active'])->toBeFalse()
        ->and(isset($model['name']))->toBeTrue()
        ->and(isset($model['nullable']))->toBeFalse()
        ->and(isset($model['missing']))->toBeFalse();
});

it('keeps requested appends on new hydrated instances', function (): void {
    $model = (new AttributeTestPost)->newInstance([
        'name' => 'Humble',
    ], ['label']);

    expect($model->getAppends())->toBe(['label'])
        ->and($model->toArray())->toMatchArray([
            'name' => 'Humble',
            'label' => 'HUMBLE',
        ]);
});

final class AttributeTestPost extends PostModel
{
    protected $postType = 'attribute-test';

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getLabelAttribute(): string
    {
        return strtoupper($this->name);
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = trim($name);
    }
}
