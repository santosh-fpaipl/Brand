<?php

namespace Fpaipl\Panel\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Spatie\Activitylog\Contracts\Activity;
use Maatwebsite\Excel\Concerns\Importable;
use Spatie\Activitylog\Facades\LogBatch;
use Error;

// This import is used to create only new record
class ModelImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    use Importable, WithoutModelEvents;

    public $model = '';
    public $datatable = '';

    public $fields;
    public $batchLog;
    public $description;

    const SUCCESS = 'success';
    const FAIL = 'fail';
    const ERROR = 'Unknown Error';

    public function __construct($model, $datatable, $description)
    {
        $this->model = $model;
        $this->datatable = $datatable;
        $this->description = $description;

        $this->fields = (new $this->datatable)->getfields()->toArray();
        $this->batchLog = array(
            'total_rows' => 0,
            'status' => '',
            'message' => '',
            'rows' => array(
                // 0 => [
                    //     'record_no' => 1,
                    //     'status' => self::FAIL,
                    //     'error' => self::ERROR,
                    //     'columns' => array(
                    //         'name' => 'Category Name',
                    //         'parent_id' => null,
                    //     ),
                    // ]
            ),
        );
    }

    public function collection(Collection $rows)
    {
        LogBatch::startBatch();
        Cache::put($this->datatable::IMPORT_BATCH_UUID, LogBatch::getUuid()); // save batch id to retrieve activities later
        $this->batchLog['total_rows'] = $rows->count();
        try {
            if($this->dataFormatValidation($rows)){
                foreach ($rows as $key => $row) {
                    $importValues = $this->batchLog['rows'][$key]['columns'];
                    if($this->dataValidation($row, $key)){
                        try {
                            $importValues = $this->model::performColumnNameMapping($importValues);
                            $importValues = $this->model::reComputeColumnValue($importValues, $row);
                            $this->model::withoutEvents(function () use($importValues, $key) {
                                $newRecord = $this->model::create($importValues);
                                $newRecord->addModelTag(json_encode($newRecord));
                                $this->setRecordResponse('New record with id:' . $newRecord->id . ' is created', self::SUCCESS, $key);
                            });
                        } catch (\Exception $e) {
                            $this->setRecordResponse($e->getMessage(), self::FAIL, $key);
                        }
                    }
                }
            } else {
                throw new Error('An error occured');
            }
        } catch (\Throwable $th) {} finally {
            $this->createImportActivityLog();
            LogBatch::endBatch();
        }
    }
    
    
    // For each row validation check
    public function dataValidation($row, $key){
        $validator = Validator::make(
            $row->toArray(), 
            $this->model::importValidationRules(),
            $this->model::validationErrosMessages()
        );
        
        if ($validator->fails()) {
            $errorMsg =  implode(" | ", $validator->errors()->all());
            $this->setRecordResponse($errorMsg, self::FAIL, $key);
            return false;
        } else {
            return true;
        }
    }

    // Set response of each import and batch import
    public function setRecordResponse($message, $status, $key){
        $this->batchLog['rows'][$key]['status'] = $status;
        $this->batchLog['rows'][$key]['message'] = $message;
    }

    // Create template of batch import response
    // 1. Exclude non-importable
    // 2. Column name must be correct & exists
    // 3. Prepare bactchLog
    public function dataFormatValidation($rows) : bool
    {
       
        foreach ($rows as $key => $row) {
            $record['record_no'] = $key + 1;
            $record['status'] = self::FAIL;
            $record['message'] = self::ERROR;

            foreach ($this->fields as $column) {
                
                if ($column['importable']) {
                    $label = Str::lower(Str::replace(' ','_', $column['labels']['export']));
                    try {
                        $record['columns'][$label] = $row[$label];
                    } catch(\Exception $e){
                        $this->batchLog['status'] = self::FAIL;
                        $this->batchLog['message'] = $e->getMessage();
                        return false;
                    }
                }
            }
            array_push($this->batchLog['rows'], $record);
        }
        return true;
    }

    /*
        It create one log for all records.
    */
    public function createImportActivityLog()
    {
        activity()
            ->event('batch')
            ->performedOn(new $this->model)
            ->causedBy(User::first()) // Auth::user()
            ->withProperties($this->batchLog)
            ->tap(function (Activity $activity) {
                $activity->log_name = $this->datatable::IMPORT_BATCH_LOG_NAME;
            })
            ->log($this->description);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headingRow(): int
    {
        return 1;
    }
}