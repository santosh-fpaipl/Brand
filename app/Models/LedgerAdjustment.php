<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Models\Ledger;
use App\Models\User;

class LedgerAdjustment extends Model 
{
    use
        Authx,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'user_id',
        'ledger_id',
        'order_qty',
        'ready_qty',
        'demand_qty',
        'note'
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

    public function ledger(){

        return $this->belongsTo(Ledger::class);
    }

    public function user(){

        return $this->belongsTo(User::class);
    }
   
    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'user_id',
                    'ledger_id',
                    'order_qty',
                    'ready_qty',
                    'demand_qty',
                    'note',
                    'created_at', 
                    'updated_at', 
            ])->useLogName('model_log');
    }
}