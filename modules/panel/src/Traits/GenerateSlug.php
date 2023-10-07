<?php

namespace Fpaipl\Panel\Traits;

use Illuminate\Support\Str;
use App\Models\Category;

trait GenerateSlug
{
    public static $categories;

    protected static function bootGenerateSlug()
    {
        static::creating(function ($model) {
            self::handle($model);
        });
        static::updating(function ($model) {
            self::handle($model);
        });
    }

    // PROBLEM : On update of parent child slug will not update

    private static function handle($model)
    {
        // First create new slug joining parent + slug(name)
        $model->slug = Str::slug($model->name);
        self::$categories = Category::withTrashed()->select('slug')->get()->toArray();
        self::checkSlug($model->slug);
        //Check occurrence of name
        $categoryCount = Category::withTrashed()
            ->where('name', $model->name)
            ->where('id', '!=', $model->id)
            ->count();

        if ($categoryCount) {
            $model->slug = $model->slug . '-' . ++$categoryCount;
        }
    }

    private static function checkSlug($slug)
    {
        echo $slug;
        // if (self::$categories->where('0', 'aaa-2')) {
        //     echo "Found";
        // } else {
        //     echo "Not Found";
        // }
        //dd(self::$categories);
    }
}