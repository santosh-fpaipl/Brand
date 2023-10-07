<?php

namespace Fpaipl\Panel\Http\Livewire;
use Livewire\Component;
use Route;

class Delete extends Component
{
    public $formType;
    
    public $recordId;

    public $model;

    public $modelClass;

    public $messages;
    
    protected $datatable;
    
    public $modelName;

    public $datatableClass;

    public $returnURL;

    public $page;

    protected $queryString = ['page'];

    public function mount()
    {
       
        $this->recordId = $this->model->id;

        $this->datatable = new $this->datatableClass();
        
        // It have model name only.
        $this->modelName = $this->datatable->getModelName();

        $this->messages = $this->datatable->getMessages();

    }

    public function delete()
    {
        if ($this->modelClass::softDeleteModelWithRelation([$this->recordId], $this->model)) {
            session()->flash('toast', [
                'class' => 'success',
                'text' => $this->messages['delete_success']
            ]);
        }
        return redirect()->route($this->returnURL,['page'=>$this->page,'from'=>'crud']);
    }

    public function render()
    {
        return view('panel::livewire.delete');
    }
}