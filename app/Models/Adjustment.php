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
use App\Models\AdjustmentItem;
use App\Models\Chat;

class Adjustment extends Model 
{
    use
        Authx,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'sid',
        'ledger_id',
        'quantity',
        'user_id',
        'type',
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

    /*
        return something like this
        DG-ADJ-0001
    */
    public static function generateId() {
        $static = 'DG-ADJUST';
        // Retrieve the last order by ID and get its ID
        $lastOrder = self::orderBy('id', 'desc')->first();
        $serial = $lastOrder ? $lastOrder->id + 1 : 1; // If there's no order, start from 1
        $serial = str_pad($serial, 4, '0', STR_PAD_LEFT); // Pad with zeros to make it at least 4 digits
        return $static . '-' . $serial;
    }   

    // Helper Functions

    public function scopeStaffAdjustments($query, $userId)
    {
        $query->where('user_id', $userId);
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeFabricatorAdjustments($query, $userId)
    {
        return $query->whereHas('ledger', function ($query) {
            $query->where('party_id', auth()->user()->party->id);
        })->orderBy('created_at', 'desc');
    }

    public function scopeManagerAdjustments($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    // Relationships

    public function ledger(){

        return $this->belongsTo(Ledger::class);
    }

    public function user(){

        return $this->belongsTo(User::class);
    }

    public function adjustmentItems(){
        return $this->hasMany(AdjustmentItem::class);
    }

    public function items()
    {
        return $this->adjustmentItems;
    }

    public function chats(){
        return $this->morphToMany(Chat::class, 'chatable');
    }
   
    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'user_id',
                    'ledger_id',
                    'quantity',
                    'type',
                    'created_at', 
                    'updated_at', 
            ])->useLogName('model_log');
    }
}