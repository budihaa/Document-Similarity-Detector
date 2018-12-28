<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Category;
use App\Models\Detect;
use App\Models\MasterDocs;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	$category = Category::count();
    	$masterDocs = MasterDocs::count();
    	$detect = Detect::count();

		$activity = DB::table('detects')
						->select(DB::raw('DATE(created_at) AS date'), DB::raw('count(*) AS counter'))
						->whereMonth('created_at', date('m'))
						->whereYear('created_at', date('Y'))
						->groupBy('date')
						->get();

        return view('pages/dashboard/home', compact('category', 'masterDocs', 'detect', 'activity'));
    }
}
