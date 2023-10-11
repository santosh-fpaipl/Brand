<?php

namespace App\Models;

use App\Models\Purchase;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model 
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
        'product_id',
        'product_sid', 
        'fabricator_id',
        'fabricator_sid',
        'sid',
        'quantity',
        'quantities',
        'message',
        'expected_at',
        'log_status_time',
        'status',
    ];
    
    protected $cascadeDeletes = [];
    protected $CascadeSoftDeletesRestore = [];
    protected $dependency = [];
    
    
    public const FINAL_STATUS = 'po_completed';
    public const STATUS = ['po_issued','po_placed','po_completed','cancelled'];
    
    public function getRouteKeyName()
    {
        return 'sid';
    }
    
    public static $cache_remember; 
    
    public static function getCacheRemember()
    {
        if (!isset(self::$cache_remember)) {
            self::$cache_remember = config('api.cache.remember');
        }

        return self::$cache_remember;
    }

    // Helper Functions
    
    /**
     * It will check that purchase of fabricator has created or not against brand purchase order.
     */
    public function fabricatorHasPurchaseFabric()
    {
        $response = Http::get(env('FABRICATOR_APP').'/api/internal/haspurchase?po_sid='.$this->sid);
        if ($response->successful()) {
            $data = json_decode($response->body(), true);
            return count($data['data'])?1:0;
        } else {
            //return 'Error: ' . $response->status();
            return 0;
        }
    }

    /**
     * It will check that saleorder of monal has created or not against brand purchase order.
     */
    public function monalHasSoldFabric()
    {
        $response = Http::get(env('MONAL_APP').'/api/saleorder/'.$this->sid.'/F1');
        if ($response->successful()) {
            $data = json_decode($response->body(), true);
            return count($data['data'])?1:0;
        } else {
            //return 'Error: ' . $response->status();
            return 0;
        }
    }

    // Relationships

    public function purchases(){
        return $this->hasMany(Purchase::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'sid',
                    'quantity',
                    'quantities',
                    'message',
                    'expected_at',
                    'log_status_time',
                    'status',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}