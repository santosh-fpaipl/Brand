<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Models\Ready;
use App\Models\Stock;

class ReadyItem extends Model 
{
    use
        Authx,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'stock_id',
        'ready_id',
        'quantity',
    ];
    
    
    protected $dependency = [];
    
   
    
    //For Cache remember time
    public static $cache_remember; 
    
    public static function getCacheRemember()
    {
        if (!isset(self::$cache_remember)) {
            self::$cache_remember = config('api.cache.remember');
        }

        return self::$cache_remember;
    }

    //End of cache remember time

    // Helper Functions
    
    // Relationships

    public function ready(){

        return $this->belongsTo(Ready::class);
    }

    public function stock(){

        return $this->belongsTo(Stock::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'stock_id',
                    'ready_id',
                    'quantity',
                    'created_at', 
                    'updated_at', 
            ])->useLogName('model_log');
    }
}