<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageMedia;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\JobWorkOrder;

class Purchase extends Model 
{
    use
        Authx,
        HasFactory,
        SoftDeletes,
        CascadeSoftDeletes,
        CascadeSoftDeletesRestore,
        LogsActivity,
        ManageMedia,
        ManageModel,
        ManageTag;

    // Properties

   //const INDEXABLE = false;

    /*
        Auto Generated Columns:
        id
        slug
    */
    protected $fillable = [
        'job_work_order_id',
        'job_work_order_sid',
        'product_id', 
        'product_sid',
        'fabricator_id',
        'fabricator_sid',
        'sale_id',
        'sale_sid',
        'sid',
        'invoice_no',
        'invoice_date',
        'quantity',
        'quantities',
        'loss_quantity',
        'loss_quantities',
        'log_status_time',
        'time_difference',
        'message',
        'status',
    ];
    
    protected $cascadeDeletes = [];

    protected $CascadeSoftDeletesRestore = [];
    
    protected $dependency = [];

    public const FINAL_STATUS = 'completed';

    public const STATUS = ['cutting','production','packing','ready','requested','dispatched','completed','cancelled'];


    public function hasDependency(){
        return count($this->dependency);
    }

    public function getDependency(){
        return $this->dependency;
    }

    public function getRouteKeyName()
    {
        return 'sid';
    }

    /*
        return something like this
        MC-SO0823-0001
    */
    public static function generateId() {
        $static = 'P-';
        $month = date('m'); // Current month
        $year = date('y'); // Last two digits of the current year
        // Retrieve the last order by ID and get its ID
        $lastOrder = self::orderBy('id', 'desc')->first();
        $serial = $lastOrder ? $lastOrder->id + 1 : 1; // If there's no order, start from 1
        $serial = str_pad($serial, 4, '0', STR_PAD_LEFT); // Pad with zeros to make it at least 4 digits
        return $static . $month . $year . '-' . $serial;
    }    
    // Helper Functions
   

    // Relationships
    
    public function jobWorkOrder(){
        return $this->belongsTo(JobWorkOrder::class);
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                    'id', 
                    'job_work_order_id',
                    'job_work_order_sid',
                    'product_id', 
                    'product_sid',
                    'fabricator_id',
                    'fabricator_sid',
                    'sale_id',
                    'sale_sid',
                    'sid',
                    'invoice_no',
                    'invoice_date',
                    'quantity',
                    'quantities',
                    'loss_quantity',
                    'loss_quantities',
                    'log_status_time',
                    'time_difference',
                    'message',
                    'status',
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}