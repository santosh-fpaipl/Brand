<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Fpaipl\Panel\Traits\Authx;
use Fpaipl\Panel\Traits\CascadeSoftDeletes;
use Fpaipl\Panel\Traits\CascadeSoftDeletesRestore;
use Fpaipl\Panel\Traits\ManageModel;
use Fpaipl\Panel\Traits\ManageTag;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Fpaipl\Panel\Traits\ManageMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\Permission\Traits\HasRoles;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Support\Facades\Validator;

class Party extends Model implements HasMedia
{
    use  
    Authx,
    SoftDeletes,
    CascadeSoftDeletes,
    CascadeSoftDeletesRestore,
    LogsActivity,
    ManageModel,
    ManageTag,
    InteractsWithMedia,
    ManageMedia,
    HasRoles;

    /*
        Auto Generated Columns:
        id
    */

    protected $fillable = [
        'user_id',
        'business',
        'gst',
        'pan',
        'sid',
        'type',
        'monaal_id',
        'info',
        'tags',
        'active',
    ];

    protected $cascadeDeletes = [];
    protected $CascadeSoftDeletesRestore = [];
    protected $dependency = [];

    public const TYPE = ['staff','fabricator','manager'];

    public function getRouteKeyName()
    {
        return 'sid';
    }

    /*
        return something like this
        DG-001
    */
    public static function generateId() {
        $static = 'DG';
        // Retrieve the last order by ID and get its ID
        $lastOrder = self::orderBy('id', 'desc')->first();
        $serial = $lastOrder ? $lastOrder->id + 1 : 1; // If there's no order, start from 1
        $serial = str_pad($serial, 3, '0', STR_PAD_LEFT); // Pad with zeros to make it at least 4 digits
        return $static . '-' . $serial;
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

    //Relationship

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Chat::class);
    }


    // Media

    /**
     * cSize is Conversion Size
     * default is s400
     */
    public function getMediaConversionName($cSize = 's400')
    {
        switch ($cSize) {
            case 's100':
                return 'party-conversion-size-100px';
            default:
                return 'party-conversion-size-400px';
        }
    }

    /**
    * cName is Collection Name
    * default is primary
    */
    public function getMediaCollectionName($cName = 'primary')
    {
        switch ($cName) {
            case 'secondary':
                return 'party-secondary-images';
            default:
                return 'party-primary-image';
        }
    }

    /**
     * 
     */
    public function getImage($cSize = '', $cName = 'primary', $withId = false)
    {
        $collection = collect();
        $allMedia = $this->getMedia($this->getMediaCollectionName($cName));
        foreach ($allMedia as $media) {
            $value = $media->getUrl(empty($cSize) ? '' : $this->getMediaConversionName($cSize));
            if ($withId) {
                $collection->put($media->id, $value);
            } else {
                $collection->push($value);
            }
        }
        // Pending optimization
        if ($cName == 'primary') {
            return $collection->first();
        } else {
            return $collection;
        }
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection($this->getMediaCollectionName('secondary'))
            ->useFallbackUrl(config('app.url') . '/storage/assets/images/placeholder.jpg')
            ->useFallbackPath(public_path('storage/assets/images/placeholder.jpg'));

        $this
            ->addMediaCollection($this->getMediaCollectionName('primary'))
            ->useFallbackUrl(config('app.url') . '/storage/assets/images/placeholder.jpg')
            ->useFallbackPath(public_path('storage/assets/images/placeholder.jpg'))
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion($this->getMediaConversionName('s100'))
            ->format(Manipulations::FORMAT_WEBP)
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->queued();

        $this->addMediaConversion($this->getMediaConversionName('s400'))
            ->format(Manipulations::FORMAT_WEBP)
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->queued();
    }

    // Logging

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id', 
                'user_id',
                'business',
                'gst',
                'pan',
                'sid',
                'type',
                'monaal_id',
                'info',
                'tags',
                'active',
                'created_at', 
                'updated_at', 
                'deleted_at'
            ])
            ->useLogName('model_log');
    }
}
