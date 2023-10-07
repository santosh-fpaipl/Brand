<?php

namespace Fpaipl\Panel\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait CascadeSoftDeletes
{
    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            $model->validateCascadingSoftDelete();
            $model->runCascadingDeletes();
        });
    }

    /**
     * Run the cascading soft delete for this model.
     *
     * @return void
     */
    protected function runCascadingDeletes()
    {
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes($relationship);
        }
    }

    protected function getActiveCascadingDeletes()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return $this->{$relationship}()->exists();
        });
    }

    protected function cascadeSoftDeletes($relationship)
    {
        $delete ='delete';
        foreach ($this->{$relationship}()->get() as $model) {
            isset($model->pivot) ? $model->pivot->{$delete}() : $model->{$delete}();
        }
    }

    /*
        This function check that this model has implement the SoftDeletes trait or not
        and also check for existence of provided relationship of model.
    */

    protected function validateCascadingSoftDelete()
    {
        if (! $this->implementsSoftDeletes()) {
             $this->softDeleteNotImplemented(get_called_class());
        }

        if ($invalidCascadingRelationships = $this->hasInvalidCascadingRelationships()) {
            throw $this->invalidRelationships($invalidCascadingRelationships);
        }
    }

    // Check this model use the SoftDeletes trait or not

    protected function implementsSoftDeletes()
    {
        return method_exists($this, 'runSoftDelete');
    }

    // Check for the provided relationship is exist or not

    protected function hasInvalidCascadingRelationships()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return ! method_exists($this, $relationship) || ! $this->{$relationship}() instanceof Relation;
        });
    }

    // Get the provided relationship of model

    protected function getCascadingDeletes()
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
    }

    //For exception handling

    public function softDeleteNotImplemented($class)
    {
        throw new \ErrorException(sprintf('%s does not implement Illuminate\Database\Eloquent\SoftDeletes', $class));
    }


    public function invalidRelationships($relationships)
    {
        throw new \ErrorException(sprintf(
            '%s [%s] must exist and return an object of type Illuminate\Database\Eloquent\Relations\Relation',
            Str::plural('Relationship', count($relationships)),
            join(', ', $relationships)
        ));
    }

    //End of exception handling
}