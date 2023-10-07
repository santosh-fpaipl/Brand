<?php

namespace Fpaipl\Panel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Fpaipl\Panel\Http\Livewire\Datatables;
use Fpaipl\Panel\Http\Livewire\Delete;
use Fpaipl\Panel\Http\Livewire\AppToast;
use Fpaipl\Panel\Http\Livewire\AlertBox;
use Fpaipl\Panel\View\Components\BulkSelect;
use Fpaipl\Panel\View\Components\SelectedRecordsAlertBox;
use Fpaipl\Panel\View\Components\DependentModel;
use Fpaipl\Panel\Datatables\ModelDatatable;

/*
Calling way  in main app:

1. Livewire Component
@livewire('app-toast')
2. Blade Component
<x-panel::alert-box />
3. Blade View
@include('panel::test')

*/

class PanelServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'panel');
        $this->loadViewComponentsAs('panel', [
            BulkSelect::class,
            SelectedRecordsAlertBox::class,
            DependentModel::class,
        ]);
        Livewire::component('app-toast', AppToast::class);
        Livewire::component('alert-box', AlertBox::class);
        Livewire::component('datatables', Datatables::class);
        Livewire::component('delete', Delete::class);
    }

    public function provides(): array
    {
        return [
            ModelDatatable::class
        ];
    }

}