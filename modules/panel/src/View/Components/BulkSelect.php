<?php

namespace Fpaipl\Panel\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BulkSelect extends Component
{
    public $modelName;
    public $labels;
    public $selectPage;
    public $selectAll;
    public $anyOptionSelected=false;

    /**
     * Create a new component instance.
     */
    public function __construct($modelName, $labels, $selectPage, $selectAll) {
       $this->modelName = $modelName;
       $this->labels = $labels;
       $this->selectPage = $selectPage;
       $this->selectAll = $selectAll;
       $this->checkOptionSelected();
    }

    public function checkOptionSelected(){

        foreach ($this->labels['options'] as $option) {
            if ($this->{$option['binding']}) {
                $this->anyOptionSelected = true;
            }
        }

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('panel::components.bulk-select');
    }
}