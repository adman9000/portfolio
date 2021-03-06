<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\Exchange;
use App\Modules\Portfolio\Transaction;
//use App\Events\PusherEvent;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    //

    public function __construct() {
         $this->middleware('auth');
    }

     public function index()
    {
        $data = array();
        $user = Auth::user();
        $data['transactions'] = $user->transactions;

        return view('dashboard.transactions.index', $data);
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
        $data['coins'] = Coin::with('latestCoinprice')->get();
        $data['exchanges'] = Exchange::all();
        return view("dashboard.transactions.create", $data);
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
            'coin_sold_id' => 'required',
            'exchange_id' => 'required',
            'coin_bought_id' => 'required']
            );

        //only safe if fillable/guarded set
        $data = request()->all();
        $data['user_id'] = Auth::id();
        $data['status'] = "complete";
        $data['uuid'] = "";
        //TODO
        $data['coin_user_id'] = 0;
        $data['btc_value'] = 0;
        $data['usd_value'] = 0;
        $data['gbp_value'] = 0;

        Transaction::create($data);

        return $this->index();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
		$transaction->load('coinSold', 'coinBought', 'scheme');
        return view("dashboard.transactions.show", array("transaction" => $transaction));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //

        $data = array();
        $data['coins'] = Coin::with('latestCoinprice')->get();
        $data['transaction'] = $transaction;
        return view("dashboard.transactions.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
         //Validate input
        $this->validate(request(), [
            'coin_sold_id' => 'required',
            'exchange_id' => 'required',
            'coin_bought_id' => 'required']
            );

        //TODO: must be better way for editing lots of fields..
        $transaction->exchange_id = $request->exchange_id;
        $transaction->coin_sold_id = $request->coin_sold_id;
        $transaction->coin_bought_id = $request->coin_bought_id;
        $transaction->amount_bought = $request->amount_bought;
        $transaction->amount_sold = $request->amount_sold;
        $transaction->exchange_rate = $request->exchange_rate;
        $transaction->fees = $request->fees;
        $transaction->save();

        return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
        $transaction->delete();
        return $this->index();
    }
}
