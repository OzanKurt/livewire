<?php

namespace Livewire\Connection;

use Livewire\Livewire;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    protected function fixPayload($payload) {
        array_walk_recursive($payload, function (&$element) {
            if ($element == 'null') {
                $element = null;
            }
        });

        return $payload;
    }

    public function handle($payload)
    {
        $payload = $this->fixPayload($payload);

        $instance = app('livewire')->activate($payload['name'], $payload['id']);

        try {
            Livewire::hydrate($instance, $payload);

            $instance->hydrate();

            foreach ($payload['actionQueue'] as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            Livewire::dispatch('failed-validation', $e->validator);

            $errors = $e->validator->errors();
        }

        $dom = $instance->output($errors ?? null);

        $response = new Fluent([
            'id' => $payload['id'],
            'name' => $payload['name'],
            'dom' => $dom,
        ]);

        Livewire::dehydrate($instance, $response);

        return $response;
    }

    public function processMessage($type, $data, $instance)
    {
        switch ($type) {
            case 'callMethod':
                $instance->callMethod($data['method'], $data['params']);
                break;
            case 'fileChanged':
                $instance->fileChanged($data['name']);
                break;
            case 'fireEvent':
                $instance->fireEvent($data['event'], $data['params']);
                break;
            default:
                break;
        }
    }
}
