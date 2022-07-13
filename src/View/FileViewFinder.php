<?php

namespace HumbleCore\View;

use Illuminate\View\FileViewFinder as IlluminateFileViewFinder;
use InvalidArgumentException;

class FileViewFinder extends IlluminateFileViewFinder {
    protected function findInPaths($name, $paths) {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->files->exists($viewPath = $path . '/' . $file)) {
                    return $viewPath;
                }
            }
        }

        if ($viewPath = $this->findInNestedPaths($name, $paths)) {
            return $viewPath;
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    protected function findInNestedPaths($name, $paths) {
        $nameArr = explode('.', $name);
        $fileName = end($nameArr);
        $name .= ".{$fileName}";

        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->files->exists($viewPath = $path . '/' . $file)) {
                    return $viewPath;
                }
            }
        }
    }
}
