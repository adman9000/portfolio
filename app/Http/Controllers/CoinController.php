<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coin;
use App\CoinPrice;
use App\Events\PusherEvent;
use Illuminate\Support\Facades\Auth;

class CoinController extends Controller
{
    //

    public function __construct() {
         $this->middleware('auth');
    }

     public function index()
    {
        $data = array();

        //Include latest prices & exclude Euro
        $coins = Coin::with('latestCoinprice')->where("code", "!=","EUR")->get();

        //Get transaction for this user - probably better way to do this!
        $user = Auth::user();
        $transactions = $user->transactions;

        $amount_owned = array();

        foreach($transactions as $transaction) {
            if(!isset($amount_owned[$transaction->coin_bought_id])) $amount_owned[$transaction->coin_bought_id] = 0;
            if(!isset($amount_owned[$transaction->coin_sold_id])) $amount_owned[$transaction->coin_sold_id] = 0;
            $amount_owned[$transaction->coin_bought_id] += $transaction->amount_bought;
            $amount_owned[$transaction->coin_sold_id] -= $transaction->amount_sold;
        }
       
        foreach($amount_owned as $coin_id=>$amount) {
            foreach($coins as $c=>$coin) {
                if($coin->id == $coin_id) $coins[$c]->amount_owned = $amount;
            }
        }
        $data['coins'] = $coins;

        return view('coins.index', $data);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view("coins.create");
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
    		'code' => 'required|min:2|max:4',
    		'name' => 'required']
    		);

        //only safe if fillable/guarded set
 		Coin::create(request()->all());

 		return $this->index();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Coin $coin)
    {
        //
        return view("coins.show", array("coin" => $coin));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Coin $coin)
    {
        //
        return view("coins.edit", array("coin" => $coin));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coin $coin)
    {
        //
         //Validate input
    	$this->validate(request(), [
    		'code' => 'required|min:2|max:4',
    		'name' => 'required']
    		);

        //TODO: must be better way for editing lots of fields..
    	$coin->code = $request->code;
		$coin->name = $request->name;
		$coin->save();

 		return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coin $coin)
    {
        //
        $coin->delete();
 		return $this->index();
    }
}
