<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\CardHolder;
use Illuminate\Http\Request;

class CardHolderController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = CardHolder::findOrFail($id);
        return response()->json($obj);
    }
}
