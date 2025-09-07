<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function all(Request $request)
    {
        $countries = Country::selectRaw('id, name, LOWER(iso2) AS code')->get();
        return response()->json($countries);
    }
}
