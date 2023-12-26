<?php

namespace App\Http\Providers;

use App\Models\Ready;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PartyCreateRequest;
use App\Http\Requests\PartyUpdateRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\PartyResource;
use App\Http\Fetchers\StoreFetcher;
use App\Models\User;
use App\Models\Party;
use App\Models\Role;
use Exception;

class PartyProvider extends Provider
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('parties');
        }
        
        $parties = Cache::remember('parties', Party::getCacheRemember(), function () use($request) {

            if ($request->has('role') && !empty($request->role)) {

                return Party::where('type', $request->role)->orderBy('created_at', 'desc')->get();
                
                // $managerRole = Role::where('name', $request->role)->first();
                // return Party::whereHas('user', function ($query) use ($managerRole) {
                //     $query->whereHas('roles', function ($roleQuery) use ($managerRole) {
                //         $roleQuery->where('role_id', $managerRole->id);
                //     });
                // })->get();

            } else {

                return Party::orderBy('created_at', 'desc')->get(); // take(5)->

            }

        });

        return ApiResponse::success(PartyResource::collection($parties));
    }
   
    /**
     * Store a newly created resource in storage.
     */
    public function store(PartyCreateRequest $request)
    {

        // Only  manager can create party
        if (!auth()->user()->isManager()) {
            return ApiResponse::error('Invalid request', 422);
        }

        DB::beginTransaction();
        try{

            $exist = Party::where('user_id', $request->user_id)->first();
            if($exist){
                throw new Exception('Party is already created of this user.');
            }

            // // Make an API call to validate the SID
            // $storeFetcherrObj = new StoreFetcher();
            // $params = '?'.$storeFetcherrObj->api_secret();
            // $body = [
            //             'sid' => $request->sid,
            //             'gst' => $request->gst,
            //             'pan' => $request->pan,
            //         ];
            // $apiResponse = $storeFetcherrObj->makeApiRequest('post', '/api/customers', $params, $body);

            // print_r($apiResponse);

            // // Check the API response for validation errors
            // if($apiResponse->status == config('api.error')){
            //     // Return validation errors received from the external API
            //     return ApiResponse::error('Some error occurred while creating customer in monnal', 404);
            // }

            $party = Party::create([
                'user_id' => $request->user_id,
                'business' => $request->business,
                'gst' => $request->gst,
                'pan' => $request->pan,
                'sid' => $request->sid,
                'type' => $request->type,
                //'monaal_id' => $apiResponse->id,
                'info' => $request->info,
                
            ]);

            if($party){
                $role = Role::where('name', $party->type)->where('guard_name','web')->first();
                if(empty($role)){
                    $role = Role::create([
                        'guard_name' => 'web',
                        'name' => $party->type
                    ]);
                }

                $party->user->assignRole($role->name);

                $party->manageTag($request->validated());
                if($request->image){
                    $party->addSingleMediaToModal($request->image);
                }
            }

            DB::commit();

        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }

        return ApiResponse::success(new PartyResource($party));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Party $party)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('party' . $party);
        }
       
        $party = Cache::remember('party' . $party, Party::getCacheRemember(), function () use ($party) {
            return $party;
        });
        
        return ApiResponse::success(new PartyResource($party));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PartyUpdateRequest $request, Party $party)
    {
        // Only  manager can update party
        if (!auth()->user()->isManager()) {
            return ApiResponse::error('Invalid request', 422);
        }
        try{
            if($request->active == 'true'){
                $active = 1;
            } else {
                $active = 0;
            }
            $party->active = $active;
            $party->save();
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Party updated successfully');
    }

//    /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(Party $party)
//     {
//         try{
//             Party::softDeleteModel(
//                 array($party->id), 
//                 'App\Models\Party'
//             );
//         } catch(\Exception $e){
//             return ApiResponse::error($e->getMessage(), 404);
//         }

//         return ApiResponse::success(null,'Record has been deleted.');
//     }
}
