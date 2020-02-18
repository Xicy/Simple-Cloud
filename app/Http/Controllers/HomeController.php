<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * @return Response
     */
    public function index()
    {
        $freeSize = human_filesize(disk_free_space(storage_path("app/public/")));
        $totalSize = human_filesize(disk_total_space(storage_path("app/public/")));
        return view('home',compact('freeSize','totalSize'));
    }

    public function registerMasternode(){
        $user = auth()->user();
        $coin = Coin::find(1);
        $keys = array_map(function($i){ return $i["pubkey"];},$coin->client->listmasternodes());
        $key =$this->validate(request(),[ "data"=>"required|string" ])["data"];
        $user->haveMasternode = in_array($key,$keys);
        $user->save();
        return back();
    }
}
