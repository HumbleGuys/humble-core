<?php

namespace HumbleCore\View;

use Illuminate\Support\Str;
use Illuminate\View\FileViewFinder as IlluminateFileViewFinder;
use InvalidArgumentException;

class FileViewFinder extends IlluminateFileViewFinder
{
    protected function findInPaths($name, $paths)
    {
        if ($viewPath = $this->findViewPath($name, $paths)) {
            return $viewPath;
        }

        if ($viewPath = $this->findInNestedPaths($name, $paths)) {
            return $viewPath;
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    protected function findInNestedPaths(string $name, array $paths)
    {
        $fileName = Str::afterLast($name, '.');
        $name .= ".{$fileName}";

        return $this->findViewPath($name, $paths);
    }

    protected function findViewPath(string $name, array $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->files->exists($viewPath = $path.'/'.$file)) {
                    return $viewPath;
                }
            }
        }
    }
}
