<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $title='hello11';

    public function mount(){
       // dump('111');
    }

    public function save(){
        //dump($this->title);
    }

    public function updated($name, $value){
        //dd($value);
    }

    public function hydrate()
    {
        // Runs at the beginning of every "subsequent" request...
        // This doesn't run on the initial request ("mount" does)...
 
       // dump('aaa');
    }
 
    public function dehydrate()
    {
        // Runs at the end of every single request...
 
       // dump('bbbb');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
