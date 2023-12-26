<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ChatResource;
use App\Http\Requests\ChatCreateRequest;
use App\Events\ReloadDataEvent;
use App\Models\Chat;
use App\Models\Ledger;
use Carbon\Carbon;

class ChatProvider extends Provider
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('chats');
        }
       
        $chats = Cache::remember('chats', Chat::getCacheRemember(), function () use($request) {
            if ($request->has('ledger_sid') && $request->ledger_sid) {
                $ledger = Ledger::where('sid', $request->ledger_sid)->first();
               return Chat::where('ledger_id', $ledger->id)->orderBy('created_at', 'desc')->get();
            } else {
                return Chat::orderBy('created_at', 'desc')->get();
            }
        });

        return ApiResponse::success(ChatResource::collection($chats));
       
    }

    public function store(ChatCreateRequest $request){

        try{

            $ledger = Ledger::where('sid', $request->ledger_sid)->first();

            $chat = Chat::create([
                'message' => $request->message,
                'ledger_id' => $ledger->id,
                'sender_id' => auth()->user()->id,
                'delivered_at' => Carbon::now(),
            ]);
            
            //To send the message to pusher
            ReloadDataEvent::dispatch(env('PUSHER_MESSAGE'));
            //End of pusher

        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new ChatResource($chat));
    }
   
}
