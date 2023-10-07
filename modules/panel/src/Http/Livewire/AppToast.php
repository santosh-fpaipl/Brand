<?php

namespace Fpaipl\Panel\Http\Livewire;

use Livewire\Component;

class AppToast extends Component
{
    public $showAlert;
    public $alertText;
    public $alertClass;

    protected $listeners = ['restore' => 'restoreAlert','delete' => 'deleteAlert'];

    public function mount()
    {
        $this->showAlert = false;

        if (session()->has('toast')) {
            $this->showAlert = true;
            $this->alertText = session('toast')['text'];
            $this->alertClass = session('toast')['class'];
        } 
    }

    public function restoreAlert($prams)
    {
        $this->showAlert = true;
        $this->alertText = $prams['text'];
        $this->alertClass = $prams['class'];
    }

    public function deleteAlert($prams)
    {
        $this->showAlert = true;
        $this->alertText = $prams['text'];
        $this->alertClass = $prams['class'];
    }

    public function closeToast(){
        $this->showAlert = false; 
    }

    public function render()
    {
        return view('panel::livewire.app-toast');
    }
}