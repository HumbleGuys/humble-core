<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Facades\Filter;

class ACFCustomArchiveLocation
{
    protected $postType;

    public function __construct($postType)
    {
        $this->postType = $postType;

        Filter::add('acf/location/rule_values/page_type', [$this, 'addPageType']);
        Filter::add('acf/location/rule_match/page_type', [$this, 'matchPageType'], 10, 3);
        Filter::add('display_post_states', [$this, 'addDisplayPostStates'], 10, 2);
    }

    public function addPageType($choices)
    {
        $archivePageText = __('Archive Page', 'humbleCore');

        $choices['archive'] = $archivePageText;

        $choices['archive_'.$this->postType->name] = $archivePageText.': '.$this->postType->labels['name'];

        return $choices;
    }

    public function matchPageType($match, $rule, $options)
    {
        if (! isset($options['post_id'])) {
            return $match;
        }

        $archiveType = app('postTypes')->getPostTypeFromArchivePageId($options['post_id']);

        if ($rule['value'] == 'archive') {
            if ($rule['operator'] == '==') {
                $match = ($archiveType);
            } elseif ($rule['operator'] == '!=') {
                $match = ! ($archiveType);
            }
        } elseif (strpos($rule['value'], 'archive_') !== false) {
            $postType = str_replace('archive_', '', $rule['value']);

            if ($rule['operator'] == '==') {
                $match = ($archiveType?->name == $postType);
            } elseif ($rule['operator'] == '!=') {
                $match = ($archiveType?->name != $postType);
            }
        }

        return $match;
    }

    public function addDisplayPostStates($postStates, $post)
    {
        if ($post->ID !== $this->postType->archivePage) {
            return $postStates;
        }

        $stateText = __('%s Archive Page', 'humbleCore');
        $stateText = sprintf($stateText, $this->postType->labels['name']);

        $postStates['custom_archive_page'] = $stateText;

        return $postStates;
    }
}
