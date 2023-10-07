<?php

namespace Fpaipl\Panel\Http\Exchanges;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Fpaipl\Panel\Imports\ModelImport;
use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Fpaipl\Panel\Exports\ModelExport;

class ModelExchange extends Controller
{
    public $datatableClass;
    public $datatable;
    public $modelName;
    public $messages;
    public $model;

    public function __construct()
    {
        $model = request()->route('model');
        if(empty($model)){
            $model="Category";
        }
        $this->datatableClass = 'App\\DataTables\\'.$model.'Datatable';
        $this->model = 'App\\Models\\'.$model; 
        $this->datatable = new $this->datatableClass();
        $this->messages = $this->datatable->getMessages();
        $this->modelName = $this->datatable->getModelName();
    }

    public function exportSample()
    {
        return Excel::download(
            new ModelExport(
                $this->model,
                $this->datatableClass,
            ),  
            ($this->datatable->filename())
        );
    }

    public function importData()
    {
        return view('panel::forms.model-import', [
            'message' =>   $this->messages,
            'modelName' => $this->modelName
        ]);
    }

    public function processImportData(Request $request)
    {
        $request->validate([
            'import_file' => ['required', 'mimes:xlsx,xls'],
            'description' => ['required', 'string', 'max:255']
        ]);

        Excel::import(new ModelImport(
            $this->model,
            $this->datatableClass,
            $request->description,
        ), request()->file('import_file'));

        $batchId = Cache::get($this->datatableClass::IMPORT_BATCH_UUID);
        if ($batchId) {           
            $batchActivities = Activity::forBatch($batchId)->get();
        } else {
            $batchActivities = collect();
        }

        Session::flash('toast', [
            'class' => 'success',
            'text' => $this->messages['import_success']
        ]);

        return view('panel::forms.model-import', [
            'message' =>   $this->messages,
            'modelName' => $this->modelName,
            'batchActivities' => $batchActivities,
        ]);
    }

    // public function import()
    // {
    //     return view('panel::forms.model-import', [
    //         'message' =>   $this->messages,
    //         'modelName' => $this->modelName
    //     ]);
    // }

    // public function process(Request $request)
    // {
    //     $request->validate([
    //         'import_file' => ['required', 'mimes:xlsx,xls'],
    //         'description' => ['required', 'string', 'max:255']
    //     ]);

    //     Excel::import(new ModelImport(
    //         $this->model,
    //         $this->datatableClass,
    //         $request->description,
    //     ), request()->file('import_file'));

    //     $batchId = Cache::get($this->datatableClass::IMPORT_BATCH_UUID);
    //     if ($batchId) {           
    //         $batchActivities = Activity::forBatch($batchId)->get();
    //     } else {
    //         $batchActivities = collect();
    //     }

    //     Session::flash('toast', [
    //         'class' => 'success',
    //         'text' => $this->messages['import_success']
    //     ]);

    //     return view('panel::forms.model-import', [
    //         'message' =>   $this->messages,
    //         'modelName' => $this->modelName,
    //         'batchActivities' => $batchActivities,
    //     ]);
    // }
}