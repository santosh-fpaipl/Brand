<?php

namespace App\Http\Providers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseProvider;

class Provider extends BaseProvider
{
    use AuthorizesRequests, ValidatesRequests;

}