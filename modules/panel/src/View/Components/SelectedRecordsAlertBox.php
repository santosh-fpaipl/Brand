<?php

namespace Fpaipl\Panel\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectedRecordsAlertBox extends Component
{
    public $type;
    public $message;

    /**
     * Create a new component instance.
     */
    public function __construct($message, $type = 'primary')
    {
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('panel::components.selected-records-alert-box');
    }
}