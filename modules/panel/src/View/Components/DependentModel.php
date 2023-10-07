<?php

namespace Fpaipl\Panel\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Illuminate\Support\Str;

class DependentModel extends Component
{
    public $model;
    public $dependentRecordCount;
    public $dependentRecords = array();

    /**
     * Create a new component instance.
     */
    public function __construct($model)
    {
        if (!$model instanceof Model) dd('Model is required as param');
        $this->model = $model;
        $this->dependentRecordCount = 0;
        $this->checkDependentModel();
    }

    public function checkDependentModel()
    {
        if ($this->model->hasDependency()) {
            foreach ($this->model->getDependency() as $relationalDependency) {
                try{
                    $relationDependencies = $this->model->$relationalDependency;
                    if(empty($relationDependencies)){
                        $relationCount = 0;
                    } else {
                        if ($relationDependencies instanceof Collection) {
                            $relationCount = $relationDependencies->count();
                        } else {
                            $relationCount = 1;
                        }
                    }
                    if ($relationCount) {
                        $modelName =  Str::of(get_class($relationDependencies->first()))->afterLast('\\');
                        $this->dependentRecordCount += $relationCount;
                        //$this->dependentRecords[$relation] = $relationCount;
                        $this->dependentRecords[strtolower($modelName)] = $relationCount;
                    }
                } catch(\Exception $e){
                    if($e->getMessage() == get_class($this->model).'::'.$relationalDependency.' must return a relationship instance.'){
                        $relationDependencies = $this->model->$relationalDependency(); 
                        $this->dependentRecords[$relationDependencies['modelName']] = $relationDependencies['total'];
                    }
                }
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('panel::components.dependent-model');
    }
}