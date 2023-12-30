<?php
namespace App\Http\Providers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserProvider extends Provider
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('users');
        }

        $users = Cache::remember('users', User::getCacheRemember(), function () use($request) {
            if($request->has('party') && !empty($request->party)){
                if($request->party == 'created'){
                    return User::has('party')->get();
                } else if($request->party == 'not_created'){
                    return User::doesntHave('party')->get();
                }   
            } else {
                return User::get();
            }
        });
        
        return ApiResponse::success(UserResource::collection($users));
    }
    
}