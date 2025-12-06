<?php
namespace App\Http\Controllers;

use App\Models\ProvinceFaEn;
use App\Models\CityFaEn;
use Illuminate\Http\Request; // برای Request

class GeoController extends Controller
{
    public function provinces(Request $request)
    {
        $countryId = $request->input('country_id');
        $provinces = ProvinceFaEn::where('country_id', $countryId)
            ->where('status_province', 1)
            ->orderBy('name_fa')
            ->get(['id','name_fa','name_en']);
        return response()->json($provinces);
    }

    public function cities(Request $request)
    {
        $provinceId = $request->input('province_id');
        $cities = CityFaEn::where('province_id', $provinceId)
            ->where('status_city', 1)
            ->orderBy('name_fa')
            ->get(['id','name_fa','name_en']);
        return response()->json($cities);
    }
}
