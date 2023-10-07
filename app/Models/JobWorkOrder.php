<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Http;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageMedia;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Fpaipl\Panel\Traits\Authx;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Purchase;

class JobWorkOrder extends Model 
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
                       // public const STATUS = ['created','procurement','production','finish','ready','requested','completed','cancelled'];

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
    // Helper Functions
    
    public function fabricatorHasPurchaseFabric(){

        $response = Http::get(env('FABRICATOR_APP').'/api/internal/haspurchase?jwo_sid='.$this->sid);
        if ($response->successful()) {
            $data = json_decode($response->body(), true);
            return count($data['data'])?1:0;
        } else {
            //return 'Error: ' . $response->status();

            return 0;

        }
    }

    public function monalHasSoldFabric(){

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
                    'created_at', 
                    'updated_at', 
                    'deleted_at'
            ])->useLogName('model_log');
    }
}