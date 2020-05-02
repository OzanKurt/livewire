<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;

trait HandlesFileUploads
{
    /**
     * Check if the file exists inside the component.
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasFile(string $name)
    {
        return $this->fileUploadManager->getDriver()->has($this, $name);
    }

    /**
     * Get the uploaded file from the driver.
     *
     * @param  string $name
     * @return \Illuminate\Http\UploadedFile|array
     */
    public function getFile(string $name)
    {
        return $this->fileUploadManager->getDriver()->get($this, $name);
    }

    /**
     * Upload the files and set them to the driver.
     *
     * @param string $name
     * @param \Illuminate\Http\UploadedFile|array $value
     */
    public function setFile(string $name, $value)
    {
        if (is_array($value)) {
            $data = [];

            foreach ($file as $file) {
                $data[] = $this->storeFile($file);
            }
        } else {
            $data = $this->storeFile($file);
        }

        return $this->fileUploadManager->getDriver()->set($this, $name, $data);
    }

    /**
     * Storea an UploadedFile to the storage and return its data as array, to be stored in a driver.
     *
     * @param  UploadedFile $file
     * @return array
     */
    private function storeFile(UploadedFile $file)
    {
        return [
            'path' => $file->store($this->getUploadPath()),
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'error' => $file->getError(),
        ];
    }

    /**
     * Get the storage path for the components uploaded files.
     *
     * @return string
     */
    private function getUploadPath()
    {
        return "livewire/components/{$this->id}/uploaded-files";
    }
}
