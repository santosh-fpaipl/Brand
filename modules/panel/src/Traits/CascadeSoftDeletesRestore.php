<?php

namespace Fpaipl\Panel\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait CascadeSoftDeletesRestore
{
    protected static function bootCascadeSoftDeletesRestore()
    {
        static::restoring(function ($model) {
            $model->validateCascadingSoftDeleteRestore();
            $model->runCascadingDeletesRestore();
        });
    }

    /**
     * Run the cascading soft delete for this model.
     *
     * @return void
     */
    protected function runCascadingDeletesRestore()
    {
        foreach ($this->getActiveCascadingDeletesRestore() as $relationship) {
            $this->cascadeSoftDeletesRestore($relationship);
        }
    }

    protected function getActiveCascadingDeletesRestore()
    {
        return array_filter($this->getCascadingDeletesRestore(), function ($relationship) {
            return $this->{$relationship}()->exists();
        });
    }

    protected function cascadeSoftDeletesRestore($relationship)
    {
        $delete ='restore';

        foreach ($this->{$relationship}()->get() as $model) {
            isset($model->pivot) ? $model->pivot->{$delete}() : $model->{$delete}();
        }
    }

    /*
        This function check that this model has implement the SoftDeletes trait or not
        and also check for existence of provided relationship of model.
    */

    protected function validateCascadingSoftDeleteRestore()
    {
        if (! $this->implementsSoftDeletesRestore()) {
             $this->softDeleteNotImplementedRestore(get_called_class());
        }

        if ($invalidCascadingRelationships = $this->hasInvalidCascadingRelationshipsRestore()) {
            throw $this->invalidRelationshipsRestore($invalidCascadingRelationships);
        }
    }

    // Check this model use the SoftDeletes trait or not

    protected function implementsSoftDeletesRestore()
    {
        return method_exists($this, 'runSoftDelete');
    }

    // Check for the provided relationship is exist or not

    protected function hasInvalidCascadingRelationshipsRestore()
    {
        return array_filter($this->getCascadingDeletesRestore(), function ($relationship) {
            return ! method_exists($this, $relationship) || ! $this->{$relationship}() instanceof Relation;
        });
    }

    // Get the provided relationship of model

    protected function getCascadingDeletesRestore()
    {
        return isset($this->CascadeSoftDeletesRestore) ? (array) $this->CascadeSoftDeletesRestore : [];
    }

    //For exception handling

    public function softDeleteNotImplementedRestore($class)
    {
        throw new \ErrorException(sprintf('%s does not implement Illuminate\Database\Eloquent\SoftDeletes', $class));
    }


    public function invalidRelationshipsRestore($relationships)
    {
        throw new \ErrorException(sprintf(
            '%s [%s] must exist and return an object of type Illuminate\Database\Eloquent\Relations\Relation',
            Str::plural('Relationship', count($relationships)),
            join(', ', $relationships)
        ));
    }

    //End of exception handling
}