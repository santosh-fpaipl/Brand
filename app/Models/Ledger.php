<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Models\Order;
use App\Models\Ready;
use App\Models\Demand;
use App\Models\Chat;
use App\Models\LedgerAdjustment;

class Ledger extends Model 
{
    use
        Authx,
        LogsActivity,
        ManageModel,
        ManageTag;

    protected $fillable = [
        'sid',
        'name',
        'product_sid',
        'product_id',
        'party_id',
        'balance_qty',
        'demandable_qty',
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
        DG-L-001
    */
    public static function generateId() {
        $static = 'DG-L';
        // Retrieve the last order by ID and get its ID
        $lastOrder = self::orderBy('id', 'desc')->first();
        $serial = $lastOrder ? $lastOrder->id + 1 : 1; // If there's no order, start from 1
        $serial = str_pad($serial, 4, '0', STR_PAD_LEFT); // Pad with zeros to make it at least 4 digits
        return $static . '-' . $serial;
    }   

    // Helper Functions
    
    public function scopeStaffLedgers($query, $productSid, $partySid = null)
    {
        $stock = Stock::where('product_sid', $productSid)->first();
        if($stock){

            $query->where('product_id',$stock->product_id);
        }

        if(!empty($partySid)){
            $party = Party::where('sid', $partySid)->first();
            $query->where('party_id', $party->party_id);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function scopeFabricatorLedgers($query, $productSid, $partyId)
    {
        $stock = Stock::where('product_sid', $productSid)->first();
        if($stock){
            $query->where('product_id' ,$stock->product_id);
        }
        $query->where('party_id', $partyId);
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeManagerLedgers($query, $productSid = null, $partySid = null)
    {
        if(!empty($productSid)){
            $stock = Stock::where('product_sid', $productSid)->first();
            $query->where('product_id',$stock->product_id);
        }

        if(!empty($partySid)){
            $party = Party::where('sid', $partySid)->first();
            $query->where('party_id', $party->party_id);
        }

        return $query->orderBy('created_at', 'desc');
    }

   

    // Relationships

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function readies(){
        return $this->hasMany(Ready::class);
    }

    public function demands(){
        return $this->hasMany(Demand::class);
    }

    public function chats(){
        return $this->hasMany(Chat::class);
    }

    public function ledgerAdjustments(){
        return $this->hasMany(LedgerAdjustment::class);
    }
    


    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'sid',
                    'name',
                    'product_sid',
                    'product_id',
                    'party_id',
                    'balance_qty',
                    'demandable_qty',
                    'user_id',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}