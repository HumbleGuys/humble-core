<?php

declare(strict_types=1);

use HumbleCore\PostTypes\PostModel;
use HumbleCore\Taxonomies\TermModel;
use Tests\Support\WordPressFunctions;

require_once __DIR__.'/Support/WordPressFunctions.php';

beforeEach(function (): void {
    WordPressFunctions::reset();
});

it('builds the default WordPress post query', function (): void {
    $posts = BuilderTestPost::withTitle()->get();

    expect($posts)->toBeEmpty()
        ->and(WordPressFunctions::$getPostsCalls)->toBe([[
            'post_type' => 'builder-test',
            'name' => null,
            'posts_per_page' => -1,
            'post_status' => ['publish'],
            'offset' => 0,
            'post__not_in' => null,
            'post__in' => null,
            'orderby' => 'post_date',
            'order' => 'desc',
            'tax_query' => null,
            's' => null,
            'suppress_filters' => false,
            'meta_query' => ['relation' => 'AND'],
        ]]);
});

it('transfers fluent scalar options to the WordPress query', function (): void {
    BuilderTestPost::name('featured')
        ->take(6)
        ->status('private')
        ->exclude([10, 20])
        ->offset(12)
        ->search('red%20shoes')
        ->orderByRandom()
        ->get();

    expect(WordPressFunctions::$getPostsCalls[0])->toMatchArray([
        'name' => 'featured',
        'posts_per_page' => 6,
        'post_status' => ['private'],
        'offset' => 12,
        'post__not_in' => [10, 20],
        'orderby' => 'rand',
        'order' => 'asc',
        's' => 'red shoes',
    ]);
});

it('builds valid WordPress meta query clauses', function (): void {
    BuilderTestPost::where('featured', true)
        ->whereDate('startDate', '<=', '20260821')
        ->get();

    expect(WordPressFunctions::$getPostsCalls[0]['meta_query'])->toBe([
        'relation' => 'AND',
        [
            'key' => 'featured',
            'value' => true,
            'type' => null,
            'compare' => '=',
        ],
        [
            'key' => 'startDate',
            'value' => '20260821',
            'type' => 'DATE',
            'compare' => '<=',
        ],
    ]);
});

it('builds safe ID queries', function (mixed $ids, array $expected): void {
    BuilderTestPost::find($ids)->get();

    expect(WordPressFunctions::$getPostsCalls[0])->toMatchArray([
        'post__in' => $expected,
        'orderby' => 'post__in',
    ]);
})->with([
    'scalar ID' => [42, [42]],
    'multiple IDs' => [[42, 43], [42, 43]],
    'empty IDs' => [[], [-1]],
]);

it('preserves status arrays and includes unpublished posts for logged-in first queries', function (): void {
    BuilderTestPost::status(['publish', 'pending'])->get();

    expect(WordPressFunctions::$getPostsCalls[0]['post_status'])->toBe(['publish', 'pending']);

    WordPressFunctions::reset();
    WordPressFunctions::$isUserLoggedIn = true;

    BuilderTestPost::first();

    expect(WordPressFunctions::$getPostsCalls[0])->toMatchArray([
        'posts_per_page' => 1,
        'post_status' => ['publish', 'draft', 'private'],
    ]);
});

it('builds valid taxonomy queries', function (): void {
    $category = new TermModel(true);
    $category->taxonomy = 'category';
    $category->id = 7;

    $tag = new TermModel(true);
    $tag->taxonomy = 'post_tag';
    $tag->id = 9;

    BuilderTestPost::whereHasTerm($category)->get();

    expect(WordPressFunctions::$getPostsCalls[0]['tax_query'])->toBe([
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => 7,
        ],
    ]);

    WordPressFunctions::reset();

    BuilderTestPost::whereInTerms(collect([$category, $tag]))->get();

    expect(WordPressFunctions::$getPostsCalls[0]['tax_query'])->toBe([
        'relation' => 'OR',
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => 7,
        ],
        [
            'taxonomy' => 'post_tag',
            'field' => 'term_id',
            'terms' => 9,
        ],
    ]);
});

it('orders by post title rather than menu order', function (): void {
    BuilderTestPost::orderByTitle()->get();

    expect(WordPressFunctions::$getPostsCalls[0])->toMatchArray([
        'orderby' => 'title',
        'order' => 'asc',
    ]);
});

final class BuilderTestPost extends PostModel
{
    protected $postType = 'builder-test';
}
