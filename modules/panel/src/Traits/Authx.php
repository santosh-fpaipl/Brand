<?php

namespace Fpaipl\Panel\Traits;

use Illuminate\Support\Str;

trait Authx
{
    public static function INDEXABLE(){
        return true;
    }

    public static function CREATEABLE(){
        return true;
    }

    public static function EDITABLE(){
        return true;
    }

    public static function VIEWABLE(){
        return true;
    }

    public function hasExtraFields() {
        return false;
    }
   
}
