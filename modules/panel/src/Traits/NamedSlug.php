<?php

namespace Fpaipl\Panel\Traits;

use Illuminate\Support\Str;

trait NamedSlug
{
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
}