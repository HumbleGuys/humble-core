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
            return $item->object_id == ! empty($this->currentObject->ID) ? $this->currentObject->ID : null;
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
}
