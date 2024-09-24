<?php

namespace HumbleCore\ACF;

use HumbleCore\Support\Jsonable;

class Image extends Jsonable
{
    public $url;

    public $alt;

    public $caption;

    public $hasWebp;

    protected $sizes;

    public function __construct($url, $alt, $caption, $sizes, $hasWebp = true)
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->caption = $caption;
        $this->sizes = $sizes;
        $this->hasWebp = $hasWebp;
    }

    public function src(string $size): string
    {
        return $this->sizes[$size];
    }

    public function webp(string $size): string
    {
        return $this->src($size).'.webp';
    }

    public function srcset(string $maxSize, bool $webp = false, string $minSize = 'medium_large'): string
    {
        return collect($this->getSizes($maxSize, $minSize))
            ->map(function ($width, $size) use ($webp) {
                return ($webp ? $this->webp($size) : $this->src($size))." {$width}";
            })
            ->join(',');
    }

    public function webpSrcSet(string $maxSize, string $minSize = 'medium_large'): string
    {
        return $this->srcset($maxSize, true, $minSize);
    }

    public function width(string $size): int
    {
        return $this->sizes["{$size}-width"];
    }

    public function height(string $size): int
    {
        return $this->sizes["{$size}-height"];
    }

    public function aspectRatio(string $size = 'hd'): string
    {
        $width = $this->width($size);
        $height = $this->height($size);

        return "{$width} / {$height}";
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'sizes' => $this->sizes,
        ];
    }

    protected function getSizes(string $maxSize, string $minSize = 'medium_large'): array
    {
        $sizes = [
            'quad_hd' => '2560w',
            'hd' => '1920w',
            'large' => '1280w',
            'medium_large' => '700w',
        ];

        $maxSizeFound = false;

        $res = [];

        foreach ($sizes as $size => $width) {
            if ($size === $maxSize) {
                $maxSizeFound = true;
            }

            if ($maxSizeFound) {
                $res[$size] = $width;
            }
        }

        if ($minSize === 'large') {
            unset($res['medium_large']);
        }

        if ($minSize === 'hd') {
            unset($res['large']);
            unset($res['medium_large']);
        }

        return $res;
    }
}
