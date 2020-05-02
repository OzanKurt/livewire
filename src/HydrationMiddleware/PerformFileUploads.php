<?php

namespace Livewire\HydrationMiddleware;

class PerformFileUploads implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        foreach ($request['actionQueue'] as $action) {
            if ($action['type'] !== 'fileChanged') return;

            $data = $action['payload'];

            // $unHydratedInstance->updating($data['name'], $data['value']);
            $unHydratedInstance->fileChanged($data['name'], $data['value']);
            // $unHydratedInstance->updated($data['name'], $data['value']);
        }
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
