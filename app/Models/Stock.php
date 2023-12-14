<?php

namespace App\Models;

use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Stock extends Model 
{
    use
        Authx,
        SoftDeletes,
        CascadeSoftDeletes,
        CascadeSoftDeletesRestore,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'sku', 
        'product_id',
        'product_sid',
        'product_option_id',
        'quantity',
        'note'
    ];
    
    protected $cascadeDeletes = [];
    protected $CascadeSoftDeletesRestore = [];
    protected $dependency = [];

    public static $cache_remember; 
    
    public static function getCacheRemember()
    {
        if (!isset(self::$cache_remember)) {
            self::$cache_remember = config('api.cache.remember');
        }

        return self::$cache_remember;
    }

    public function getRouteKeyName()
    {
        return 'product_sid';
    }

    // Helper Functions
   

    // Relationships
    
    

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'sku', 
                    'quantity',
                    'product_sid',
                    'product_id',
                    'product_option_id',
                    'active',
                    'note',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}