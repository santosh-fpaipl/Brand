<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\Authx;
use Fpaipl\Panel\Traits\ManageTag;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;
use App\Models\Address;

class Supplier extends Model
{
    use
        Authx,
        HasFactory,
        SoftDeletes,
        CascadeSoftDeletes,
        CascadeSoftDeletesRestore,
        LogsActivity,
        ManageModel,
        ManageTag;

    // Properties

    /*
        Auto Generated Columns:
        id
    */

    protected $fillable = [
        'user_id',
        'business_name',
        'gst',
        'pan',
        'sid',
        'description',
        'tags'
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
   
    

    // Helper Functions

      

    // Relationships

    public function user(){

        return $this->belongsTo(User::class);
    }

    
    public function addresses(){

        return $this->hasMany(Address::class);

    }

   
   
    //End of Relationships

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id', 
                'user_id',
                'business_name',
                'gst',
                'pan',
                'sid',
                'description',
                'tags',
                'created_at', 
                'updated_at', 
                'deleted_at'
            ])
            ->useLogName('model_log');
    }
}

