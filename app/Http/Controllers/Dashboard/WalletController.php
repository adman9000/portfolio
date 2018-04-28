<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\Wallet;

class WalletController extends Controller
{
    //

    public function __construct() {
         $this->middleware('auth');
    }

     public function index()
    {
        $data = array();
        $user = Auth::user();
        $data['wallets'] = $user->wallets;

        return view('dashboard.wallets.index', $data);
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $data = array();
        $data['coins'] = Coin::with('latestCoinprice')->orderBy('code', 'asc')->get();
        return view("dashboard.wallets.create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         //Validate input
    	$this->validate(request(), [
    		'coin_id' => 'required',
    		'balance' => 'required']
    		);

        $data = request()->all();
        $data['user_id'] =  Auth::id();

        $coin = Coin::with('latestCoinprice')->find($data['coin_id']);


        if($coin) {
            $btc_price = $coin->latestCoinprice['btc_price'];
            $usd_price = $coin->latestCoinprice['usd_price'];
            $gbp_price = $coin->latestCoinprice['gbp_price'];

            $data['btc_value'] = $data['balance'] * $btc_price;
            $data['usd_value'] = $data['balance'] * $usd_price;
            $data['gbp_value'] = $data['balance'] * $gbp_price;
        }

        //only safe if fillable/guarded set
 		Wallet::create($data);

 		return $this->index();
    }

    /**
     * Display the specified coin.
     * IN PROGRESS: Default display shows coinmarketcap data and list of exchanges & latest price at each
     * TODO: If logged in you also get your balance at each exchange plus cold storage
     * TODO: If you have the trade permission you get trade buttons
     * TODO: If you have the autotrade permission you get schemes this coin is in
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Wallet $wallet)
    {

        $data['wallet'] = $wallet;

        return view("dashboard.wallets.show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Wallet $wallet)
    {
        //
        $data = array();
        $data['coins'] = Coin::with('latestCoinprice')->get();
        $data['wallet'] = $wallet;
        return view("dashboard.wallets.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wallet $wallet)
    {
        //
         //Validate input
    	$this->validate(request(), [
            'balance' => 'required']
    		);

        //TODO: must be better way for editing lots of fields..use fill
		$wallet->fill($request->post());
		$wallet->save();

 		return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wallet $wallet)
    {
        //delete wallet values first
        DB::delete("DELETE FROM wallet_values WHERE wallet_id=".$wallet->id);

        $wallet->delete();
 		return $this->index();
    }
}
