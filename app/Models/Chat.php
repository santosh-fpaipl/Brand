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
use App\Models\Ready;
use App\Models\Demand;
use App\Models\Adjustment;

class Chat extends Model 
{
    use
        Authx,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'message',
        'type',
        'type_model_id',
        'ledger_id',
        'sender_id',
        'delivered_at',
        'read_at',
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

    public function user(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function ledger(){
        return $this->belongsTo(Ledger::class);
    }

    public function orders()
    {
        return $this->morphedByMany(Order::class, 'chatable');
    }

    public function readies()
    {
        return $this->morphedByMany(Ready::class, 'chatable');
    }

    public function demands()
    {
        return $this->morphedByMany(Demand::class, 'chatable');
    }

    public function adjustments()
    {
        return $this->morphedByMany(Adjustment::class, 'chatable');
    }

    public function ledgers()
    {
        return $this->morphedByMany(Ledger::class, 'chatable');
    }

    public function chatable(){
        return $this->hasOne(Chatable::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'message',
                    'type',
                    'type_model_id',
                    'ledger_id',
                    'sender_id',
                    'delivered_at',
                    'read_at',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}