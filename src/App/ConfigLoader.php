<?php

namespace HumbleCore\App;

class ConfigLoader
{
    public function loadConfig()
    {
        $this->filePaths()->each(function ($filePath, $key) {
            config()->set($key, require $filePath);
        });
    }

    protected function filePaths()
    {
        return collect($this->allFiles())->mapWithKeys(function ($file, $key) {
            return [$this->fileName($file) => $this->realFilePath($file)];
        });
    }

    protected function allFiles()
    {
        return app('files')->files(configPath());
    }

    protected function fileName($file)
    {
        return str_replace('.php', '', $file->getRelativePathName());
    }

    protected function realFilePath($file)
    {
        return app()->configPath($file->getRelativePathName());
    }
}
