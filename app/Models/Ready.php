<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Models\User;
use App\Models\ReadyItem;
use App\Models\Ledger;

class Ready extends Model 
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
        'sid',
        'ledger_id',
        'quantity',
        'user_id',
    ];
    
    protected $cascadeDeletes = [];
    protected $CascadeSoftDeletesRestore = [];
    protected $dependency = [];
    
    public function getRouteKeyName()
    {
        return 'sid';
    }
    
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


    /*
        return something like this
        DG-001
    */
    public static function generateId() {
        $static = 'DG-REDY';
        // Retrieve the last order by ID and get its ID
        $lastOrder = self::orderBy('id', 'desc')->first();
        $serial = $lastOrder ? $lastOrder->id + 1 : 1; // If there's no order, start from 1
        $serial = str_pad($serial, 4, '0', STR_PAD_LEFT); // Pad with zeros to make it at least 4 digits
        return $static . '-' . $serial;
    }   

    // Helper Functions

    public function scopeStaffRedies($query, $userId, $ledgerSid = null)
    {
        if(!empty($ledgerSid)){
            $query->where('ledger_sid', $ledgerSid);
        }

        return $query->whereHas('ledger', function ($query){
        })->whereHas('orders', function($query) use($userId){
            $query->where('user_id', $userId);
        })->orderBy('created_at', 'desc');

    }

    public function scopeFabricatorRedies($query, $userId, $ledgerSid = null)
    {
        $query->where('user_id', $userId);
        if(!empty($ledgerSid)){
            $query->where('ledger_sid', $ledgerSid);
        }
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeManagerRedies($query, $ledgerSid = null)
    {
        if(!empty($ledgerSid)){
            $query->where('ledger_sid', $ledgerSid);
        }
        
        return $query->orderBy('created_at', 'desc');
    }
  
    // Relationships

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function ledger(){
        return $this->belongsTo(Ledger::class);
    }

    public function readyItems(){
        return $this->hasMany(ReadyItem::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'sid',
                    'ledger_id',
                    'quantity',
                    'user_id',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}