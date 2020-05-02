<?php

namespace Livewire;

use Illuminate\Support\Manager;

class FileUploadManager extends Manager
{

    /**
     * Get the default livewire file upload driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['livewire.file_upload.driver'] ?? 'cache';
    }

    /**
     * Create a cache driver instance.
     *
     * @param  array  $config
     */
    protected function createCacheDriver(array $config)
    {
        return new CacheDriver;
    }
}
