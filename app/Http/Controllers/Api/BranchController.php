<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Branch;
use App\Http\Helpers\ApiHelper;

class BranchController extends Controller
{

    public function getBranchs(Request $request)
    {
        $branches = Branch::where('is_archived',0)->get();
        return response()->json($branches);
    }
}