<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Sanctum\HasApiTokens;
use Fpaipl\Panel\Traits\Authx;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Party;
use App\Models\Order;
use App\Models\Ready;
use App\Models\Demand;
use App\Models\LedgerAdjustment;
use App\Models\Chat;

class User extends Authenticatable implements MustVerifyEmail 
{
    use 
        Authx,
        HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles,
        SoftDeletes,
        CascadeSoftDeletes,
        CascadeSoftDeletesRestore,
        LogsActivity,
        ManageModel;

        const VIEWABLE = false;

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = [
            'name',
            'email',
            'password',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

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

    protected $cascadeDeletes = ['profile'];

    protected $CascadeSoftDeletesRestore = ['profileWithTrashed'];

    protected $dependency = ['profile','cart','cartProducts','orders'];

    public function profileWithTrashed()
    {
        return $this->profile();
    }

    //Helper functions

    public function isManager(){

        return auth()->user()->hasRole('manager');
    }

    public function isStaff(){

        return auth()->user()->hasRole('staff');
    }
    public function isFabricator(){

        return auth()->user()->hasRole('fabricator');
    }


    // public function canCreate(){

    //     return auth()->user()->hasRole(['staff', 'manager']);
    // }

    //Relationship

    public function party(): HasOne
    {
        return $this->hasOne(Party::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function readies()
    {
        return $this->hasMany(Ready::class);
    }

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function addresses(){
        return $this->hasMany(Address::class);
    }

    public function ledgerAdjustments(){
        return $this->hasMany(LedgerAdjustment::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id', 'name', 'email', 'created_at', 'updated_at', 'deleted_at'])
            ->useLogName('model_log');
    }
   
}