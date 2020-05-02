<?php

namespace Livewire\FileUpload;

use Livewire\Component;
use Illuminate\Support\Manager;
use Illuminate\Http\UploadedFile;

class CacheDriver implements FileUploadContract
{
    public $defaultCacheDuration = 60;

    public function has(Component $component, string $name) {
        return cache()->has(
            $this->generateCacheKey($component, $name)
        );
    }

    public function get(Component $component, string $name) {
        $value = cache(
            $this->generateCacheKey($component, $name)
        );

        if (! $value) {
            return null;
        }

        // Check if the $value only contains a single files information
        if (array_key_exists('path', $value)) {
            return $this->convertArrayToUploadedFile($value);
        }

        $files = [];

        foreach ($value as $data) {
            $files[] = $this->convertArrayToUploadedFile($data);
        }

        return $files;
    }

    public function set(Component $component, string $name, array $data) {
        cache([
            $this->generateCacheKey($component, $name) => $data,
        ], $this->defaultCacheDuration * 60);
    }

    /**
     * Generate a cache key for a given component and the field name.
     *
     * @param  Component $component
     * @param  string    $name
     * @return string
     */
    private function generateCacheKey(Component $component, string $name)
    {
        return "components.{$component->id}.uploaded-files.{$name}";
    }

    /**
     * Convert an array to an UploadedFile instance.
     *
     * @param  array  $data
     * @return \Illuminate\Http\UploadedFile
     */
    private function convertArrayToUploadedFile(array $data)
    {
        return new UploadedFile(
            storage_path("app/{$data['path']}"),
            $data['originalName'],
            $data['mimeType'],
            $data['error']
        );
    }

    /**
     * Set a default cache duration for the driver.
     *
     * @param int $duration
     */
    public function setDefaultCacheDuration(int $duration)
    {
        $this->defaultCacheDuration = $duration;

        return $this;
    }
}
