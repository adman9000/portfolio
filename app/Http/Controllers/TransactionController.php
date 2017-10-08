<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coin;
use App\Transaction;
use App\Events\PusherEvent;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    //

    public function __construct() {
         $this->middleware('auth');
    }

     public function index()
    {
         $this->middleware('auth');
        $data = array();
        $user = Auth::user();
        $data['transactions'] = $user->transactions;

        return view('transactions.index', $data);
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
        return view("transactions.create", $data);
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
            'coin_bought_id' => 'required']
            );

        //only safe if fillable/guarded set
        $data = request()->all();
        $data['user_id'] = Auth::id();
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
        return view("transactions.show", array("transaction" => $transaction));
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
        return view("transactions.edit", $data);
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
            'coin_bought_id' => 'required']
            );

        //TODO: must be better way for editing lots of fields..
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
