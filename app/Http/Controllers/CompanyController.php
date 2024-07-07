<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\CompanyModel;

class CompanyController extends Controller
{

    // get all companies
    public function getCompanies($id)
    {
try{
    $companies = CompanyModel::where('c_category_id',$id)->with(['user' => function ($query) {
        $locale = app()->getLocale();
        $query->select('u_id',  $locale == "ar" ? 'u_name_ar' : 'u_name', );
    }])->get();
    return $companies;

}catch(\Throwable $th){
    return response()->json([
        'status' => false,
        'message' => [
            __('auth.internal_server_error'),
            $th->getMessage()
        ]
    ], 500);
}



    }

    // get all categories
    public function getCategories()
    {

        $categories = DB::table('company_categories')->select(
            'cc_id',
            'cc_name_ar',
            'cc_name',
            'cc_image',
            'created_at',
            'updated_at'
        )->get();

        return $categories;
    }
}
