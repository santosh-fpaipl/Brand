<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Models\User;
use App\Models\Ledger;
use App\Models\Order;

class Chatable extends Model 
{
    use
        Authx,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'chat_id',
        'chatable_type',
        'chatable_id',
    ];
    
    protected $cascadeDeletes = [];
    protected $CascadeSoftDeletesRestore = [];
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

    // public function user(){
    //     return $this->belongsTo(User::class, 'sender_id');
    // }

    // public function ledger(){
    //     return $this->belongsTo(Ledger::class);
    // }

    // public function orders()
    // {
    //     return $this->morphedByMany(Order::class, 'chatable');
    // }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'chat_id',
                    'chatable_type',
                    'chatable_id',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}