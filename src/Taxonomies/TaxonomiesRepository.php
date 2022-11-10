<?php

namespace HumbleCore\Taxonomies;

use Illuminate\Support\Collection;

class TaxonomiesRepository
{
    protected array $taxnomies = [];

    public function loadTaxonomiesFrom(string $path): void
    {
        collect(app('files')->files($path))->each(function ($file) {
            $name = str_replace('.php', '', $file->getFilename());

            $class = str(str_replace(templatePath('app'), '', $file->getPath()))
                ->replace('/', '\\')
                ->prepend('\\App')
                ->append("\\{$name}")
                ->value();

            $this->register(new $class);
        });
    }

    public function register(Taxonomy $taxonomy): void
    {
        $this->taxnomies[] = $taxonomy->register();
    }

    public function getTaxonomy(string $name): ?Taxonomy
    {
        return $this->getTaxonomies()->firstWhere('name', $name);
    }

    public function getTaxonomies(): Collection
    {
        return collect($this->taxnomies);
    }

    public function addSortToPostCategories($categoriesModel)
    {
        $categoriesTax = new class extends Taxonomy
        {
            public string $name = 'category';

            public array $postTypes = ['post'];

            public array $labels = [
                'name' => 'Kategorier',
                'singular_name' => 'Kategori',
            ];

            public bool $sortable = true;
        };

        $categoriesTax->model = $categoriesModel;

        new TermSorter($categoriesTax);
    }
}
