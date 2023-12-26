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
use App\Models\OrderItem;
use App\Models\ReadyItem;
use App\Models\DemandItem;

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
        'product_option_sid',
        'product_range_id',
        'product_range_sid',
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
    
    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

    public function readyItems(){
        return $this->hasMany(ReadyItem::class);
    }

    public function demandItems(){
        return $this->hasMany(DemandItem::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'sku', 
                    'product_id',
                    'product_sid',
                    'product_option_id',
                    'product_option_sid',
                    'product_range_id',
                    'product_range_sid',
                    'quantity',
                    'note',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}