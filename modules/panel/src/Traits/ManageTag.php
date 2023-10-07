<?php

namespace Fpaipl\Panel\Traits;

use PhpParser\Node\Expr\Cast\Bool_;

trait ManageTag
{
    //We are storing selected fields in comma separated manner
    public function manageTag(array $data): bool
    {
        if(!empty($data['_token'])){
            unset($data['_token']);
        }
        if(!empty($data['image'])){
            unset($data['image']);
        }
        if(!empty($data['images'])){
            unset($data['images']);
        }
        $this->tags = implode(",", $data);
        if($this->save()) return true; else return false;
    }

    // We are storing json string of complete model
    public function addModelTag(string $model): bool
    {
        $this->tags = $model;
        if($this->save()) return true; else return false;
    }

   
}