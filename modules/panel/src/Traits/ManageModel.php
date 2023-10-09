<?php

namespace Fpaipl\Panel\Traits;
use Illuminate\Database\Eloquent\Collection;

trait ManageModel
{
    public function hasDependency(){
        return count($this->dependency);
    }

    public function getDependency(){
        return $this->dependency;
    }
    
    public static function softDeleteModel(array $records, string $model): String
    {
        $hasDependentRecord = false;

        if (!empty($records)) {
            foreach($records as $record){
                $Model = $model::findorfail($record);
                if($Model->hasDependency()){
                    foreach($Model->getDependency() as $relationalDependency){
                        try
                        { 
                            $relationDependencies = $Model->$relationalDependency;

                            if(empty($relationDependencies)){
                                $relationCount = 0;
                            } else {
                                if ($relationDependencies instanceof Collection) {
                                    $relationCount = $relationDependencies->count();
                                } else {
                                    $relationCount = 1;
                                }
                            }
                        } catch(\Exception $e){
                            if($e->getMessage() == get_class($Model).'::'.$relationalDependency.' must return a relationship instance.'){
                                $relationDependencies = $Model->$relationalDependency();
                                $relationCount = $relationDependencies['total'];
                            }
                        }

                        if($relationCount){
                            $hasDependentRecord = true;
                            break;
                        }
                    }
                }
                if($hasDependentRecord){
                    break;
                }
            }

            if($hasDependentRecord){
               return 'dependent';
            } else {
                return $model::destroy($records) ? 'success' : 'failure';
            }
        }
    }

    public static function softDeleteModelWithRelation($selectedRecords, $model)
    {
        if (!empty($selectedRecords)) {
            return $model::destroy($selectedRecords);
        }
    }

    public static function restoreModel($selectedRecords, $model)
    {
        if (is_array($selectedRecords)) {
            foreach ($selectedRecords as $id) {
                self::restoreRecord($id, $model);
            }
        } else {
            if (!empty($selectedRecords)) {
                self::restoreRecord($selectedRecords, $model);
            }
        }
    }

    private static function restoreRecord($id, $model)
    {
        $restoreModel = $model::withTrashed()->findOrFail($id);
        return $restoreModel->restore();
    }
}