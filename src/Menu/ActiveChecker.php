<?php

namespace HumbleCore\Menu;

class ActiveChecker
{
    public $currentObject;

    public $currentItems;

    public $postArchiveUrl;

    public function __construct($menuItems)
    {
        $this->currentObject = get_queried_object();

        $this->currentItems = $menuItems->filter(function ($item) {
            return $item->object_id == (isset($this->currentObject->ID) ? $this->currentObject->ID : null);
        })->pluck('ID')->all();

        $this->postArchiveUrl = get_the_permalink(get_option('page_for_posts'));
    }

    public function isActive($menuItem)
    {
        if ($this->inCurrentItems($menuItem)) {
            return true;
        }

        if ($this->isActivePostArchive($menuItem)) {
            return true;
        }

        if ($this->isActiveCustomArchive($menuItem)) {
            return true;
        }

        return false;
    }

    protected function inCurrentItems($menuItem)
    {
        return in_array((int) $menuItem->ID, $this->currentItems);
    }

    protected function isActivePostArchive($menuItem)
    {
        if (empty($this->currentObject->post_type) || $this->currentObject->post_type !== 'post') {
            return false;
        }

        if ($menuItem->url !== $this->postArchiveUrl) {
            return false;
        }

        return true;
    }

    protected function isActiveCustomArchive($menuItem)
    {
        if (! $this->currentObject instanceof \WP_Post_Type && empty($this->currentObject->post_type) && ! $this->currentObject instanceof \WP_Term) {
            return false;
        }

        $postType = null;

        if ($this->currentObject instanceof \WP_Post_Type) {
            $postType = $this->currentObject->name;
        } elseif (! empty($this->currentObject->post_type)) {
            $postType = $this->currentObject->post_type;
        } elseif ($this->currentObject instanceof \WP_Term) {
            return $this->checkCustomArchiveTaxonomy($this->currentObject->taxonomy, $menuItem);
        }

        return $this->isCustomPostTypeArchive($postType, $menuItem);
    }

    protected function checkCustomArchiveTaxonomy($taxonomyName, $menuItem)
    {
        $taxonomy = app('taxonomies')->getTaxonomy($taxonomyName);

        foreach ($taxonomy->postTypes as $postType) {
            if ($this->isCustomPostTypeArchive($postType, $menuItem)) {
                return true;
            }
        }

        return false;
    }

    protected function isCustomPostTypeArchive($postType, $menuItem)
    {
        if (empty($postType)) {
            return false;
        }

        if ($postType === 'post' || $postType === 'page') {
            return false;
        }

        return get_post_type_archive_link($postType) === $menuItem->url;
    }
}
