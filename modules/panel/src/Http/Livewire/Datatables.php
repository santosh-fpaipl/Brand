<?php

namespace Fpaipl\Panel\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Fpaipl\Panel\Exports\ModelCustomExport;


class Datatables extends Component
{
    // Pagination
    use WithPagination;
    public $pageLength;
    public $currentPage;

    // Sorting
    public $sortByColumn;
    public $sortInOrder;
    public $sortSelect;
    public $sort_select_default = 'name#asc';

    public $search;

    // Model Columns 
    public $selectedColumns;

    // Bulk Action
    public $selectAll;
    public $selectPage;
    public $bulkDisabled;
    public $selectedRecords;
    protected $renderedRecords;
    public $filteredRecords;
    protected $preQuery;


    // livewire default (dont change)
    protected $paginationTheme = 'bootstrap';

    public $fields;
    public $features;
    public $buttonsTop;
    public $buttonsTable;
    public $messages;
    protected $datatable;
    public $restoreRecordId;

    // Maintain pagination after edit of record
    public $from='';
    protected $queryString = ['from'=> ['except' =>'']];

    // Redirect Route

    public $route;

    public $modelName; // It have model name only.

    public $activePage;

    public $visiblefields;


    // Blade Boolean
    public $rowActionsEnabled;

    public $model;
    public $datatableClass;

    public function mount()
    {
        $this->prepareModelDatatable();
        $this->setInitialDefaults();
        $this->fetchData();
    }

    public function prepareModelDatatable()
    {
        $this->datatable = new $this->datatableClass();
        $this->sort_select_default = $this->datatableClass::SORT_SELECT_DEFAULT;
        $this->features = $this->datatable->features();
        $this->buttonsTop = $this->datatable->buttons('top');
        $this->buttonsTable = $this->datatable->buttons('table');
       // $this->fields = $this->datatable->getfields()->toArray();
       $this->fields = $this->datatable->getColumns();
       $this->messages = $this->datatable->getMessages();
       $this->modelName = $this->datatable->getModelName();
    }

    public function setInitialDefaults()
    {
        $this->activePage ='active';
        $this->rowActionsEnabled = $this->features['row_actions']['show']['view'][$this->activePage] || $this->panel['row_actions']['show']['edit'] || $this->panel['row_actions']['show']['delete'];
        $this->setRefreshState();
    }

    public function setRefreshState()
    {
        $this->bulkDisabled = true;
        $this->selectedColumns = [];
        $this->selectPage = false;
        $this->selectAll = false;
        $this->selectedRecords = [];
        $this->filteredRecords;
        $this->sortSelect = $this->sort_select_default;
        $this->updatedSortSelect($this->sortSelect);
        $this->managePagination();
        $this->refreshOnEveryUpdate();
    }

    public function refreshOnEveryUpdate()
    {
        $this->visiblefields = $this->computeVisibleFieldsCount();
    }

    public function updated()
    {
        $this->refreshOnEveryUpdate();
    }

    public function managePagination()
    {
        if ($this->features['pagination']['show']) {
            $this->pageLength = config('settings.per_page_count.default');
        } 
        else {
            $this->pageLength = 'all'; 
        }
    }

    public function fetchData($flag = '')
    {
        /**
         * It need to be set on every request, because:
         * 1. To maintain page history while visiting external routes
         * 2. To render global serial number on each row.
         */

        $this->currentPage = $this->page;

        // Default Query
         $query = $this->datatableClass::baseQuery($this->model);
         //$query = $this->model::query();
        
        if($this->activePage == 'trash'){
          $query=$query->onlyTrashed();
        }

        // Default Sorted     
        $query = $query->orderby($this->sortByColumn, $this->sortInOrder);

        // Search Query
        if($this->features['search']['show'][$this->activePage]){
            if (!empty($this->search)) {
                $query->where('tags', 'like', '%' . $this->search . '%');
            }
        }
        

        // Execute Query
        if ($flag == 'export') {
            $datas = $this->exportQuery($query);
        } elseif ($flag == 'bulk') {
            $datas = $query->get();
        } else {
            if ($this->pageLength == 'all') {
                $datas = $query->get();
                $this->pageLength = count($datas);
            } else {
                $datas = $query->paginate($this->pageLength);
            }
        }

        // To be used on bulk delete
        $this->filteredRecords = $datas->pluck('id')->toArray();
        $this->renderedRecords = $datas;
        return $datas;
    }

    public function exportQuery($query)
    {
        $this->selectedColumns=[];
        // $query = $query->addselect('id');
        // array_push($this->selectedColumns,  $this->fields['serial']['labels']['export']);

        foreach ($this->fields as $field) {
            if ($field['exportable'][$this->activePage] && $field['viewable'][$this->activePage] ) {
                $query = $query->addselect($field['name']);
                array_push($this->selectedColumns, Str::title($field['labels']['export']));
            }
        }
    
        return $query->get();
    }

    public function updatedSortSelect($value)
    {
        if (empty($value)) {
            $this->sortSelect = $this->sort_select_default;
            return $this->updatedSortSelect($this->sortSelect);
        }
        $this->sortByColumn = Str::before($value, '#');
        $this->sortInOrder = Str::after($value, '#');
        if(empty($this->from)){
            $this->resetPage();
        } else {
            $this->from = '';
        }
    }

    public function toggleSort($value = null)
    {
        //dd($value);
        $this->sortSelect = $value;
        $this->sortByColumn = Str::before($value, '#');
        $this->sortInOrder = Str::after($value, '#');
    }

    public function computeVisibleFieldsCount()
    {
        $count = 0;
        if ($this->features['bulk_actions']['show']['active']) {
            $count = $count + 1;
        }
        if ($this->rowActionsEnabled) {
            $count = $count + 1;
        }
        foreach ($this->fields as $field) {
            if ($field['viewable'][$this->activePage]) {
                $count++;
            }
        }
        return $count;
    }

    public function export()
    {
        return Excel::download(
            new ModelCustomExport(
                $this->model,
                $this->datatableClass,
                $this->fetchData('export'),
                $this->selectedColumns
            ),
            (new $this->datatableClass())->filename()
        );
    }

    public function escapeKeyDetect() {
       $this->search='';
    }

    public function toggleTrash($activePage){
        $this->activePage = $activePage;
        $this->setRefreshState();

    }

    public function setRoute($route){

        session(['redirect_route' => $route]);

    }

    // To restore the deleted records

    public function restoreSelectedRecords(){

        $this->model::restoreModel($this->selectedRecords, $this->model);
        $this->emit('restore', [
            'class' => 'success',
            'text' => $this->messages['restore_success']
        ]);
        $this->setRefreshState();

    }

    // To delete the records

    public function deleteSelectedRecord()
    {
        $response = $this->model::softDeleteModel($this->selectedRecords, $this->model);
        switch ($response) {
            case 'dependent':
                $this->emit('delete', [
                    'class' => 'danger',
                    'text' => $this->messages['has_dependency']
                ]);
                break;
            case 'success':
                $this->emit('delete', [
                    'class' => 'success',
                    'text' => $this->messages['delete_success']
                ]);
                break;    
            default: // failure
                $this->emit('delete', [
                    'class' => 'danger',
                    'text' => $this->messages['delete_error']
                ]);
                break;
        }
       
        $this->setRefreshState();

    }


    // ---------------------------

    public function updatedSelectedRecords()
    {
        $this->bulkDisabled = count($this->selectedRecords) < 1;
    }

    public function updatingSelectPage($value)
    {
        if ($value) {
            $this->selectedRecords = $this->filteredRecords;
        } else {
            $this->selectedRecords = [];
            $this->selectAll = false;
        }
        $this->updatedSelectedRecords();
    }

    public function updatingSelectAll($value)
    {
        if ($value) {
            $this->fetchData('bulk');
            $this->selectedRecords = $this->filteredRecords;
        } else {
            $this->selectedRecords = [];
            $this->selectAll = false;
        }
        $this->updatedSelectedRecords();
    }

    public function render()
    {
        return view('panel::livewire.datatables', [
            'data' => $this->fetchData()
        ]);
    }
}