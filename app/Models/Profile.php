<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fpaipl\Panel\Traits\Authx;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;

class Profile extends Model
{
    use
        Authx,
        HasFactory,
        HasRoles,
        SoftDeletes,
        CascadeSoftDeletes,
        CascadeSoftDeletesRestore,
        LogsActivity,
        ManageModel,
        ManageTag;

    const VIEWABLE = false;

    /*
        Auto Generated Columns:
        id
    */
    protected $fillable = [
        'user_id',
        'mobile',
        'address',
        'company',
        'c_address',
        'gst_no',
        'pan_no',
    ];
   
    protected $cascadeDeletes = [];

    protected $CascadeSoftDeletesRestore = [];

    protected $dependency = ['user'];

    public function hasDependency()
    {
        return count($this->dependency);
    }

    public function getDependency()
    {
        return $this->dependency;
    }

    public function getUserData(){
        if(empty($this->user->name)){
            return true;
        } else {
            return $this->user->name;
        }
    }

   
    //Relationship

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userWithTrashed()
    {
        return $this->user()->withTrashed();
    }


    // Helper Functions

    public function getTimestamp($value)
    {
        return getTimestamp($this->$value);
    }

    public function getValue($key)
    {

        return $this->$key;
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id', 'user_id', 'mobile', 'address', 'company', 'c_address', 'gst_no', 'pan_no', 'created_at', 'updated_at', 'deleted_at'])
            ->useLogName('model_log');
    }

}
