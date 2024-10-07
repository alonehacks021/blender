<?php

namespace Nhd\Foundation\Foundation\Livewire;

trait Events {
    public function dispatchBrowserEvent($event, $data = null) {
        if(method_exists(parent::class, 'dispatchBrowserEvent')) {
            parent::dispatchBrowserEvent($event, $data);
        }
        else {
            $this->dispatch($event, $data);
        }
    } 
}