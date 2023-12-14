<?php

namespace App\Observers;
use App\Events\ReloadDataEvent;

class BaseObserver
{
    /**
     * Handle the PurchaseOrder "created" event.
     */
    public function created($model): void
    {
        //To send the message to pusher
            ReloadDataEvent::dispatch(env('PUSHER_MESSAGE'));
        //End of pusher
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated($model): void
    {
        //print_r($model);
        
    }

    /**
     * Handle the PurchaseOrder "deleted" event.
     */
    public function deleted($model): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "restored" event.
     */
    public function restored($model): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "force deleted" event.
     */
    public function forceDeleted($model): void
    {
        //
    }
}
