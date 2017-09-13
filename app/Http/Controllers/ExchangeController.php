<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use adman9000\kraken\KrakenAPIFacade;
use Pepijnolivier\Bittrex\Bittrex;

class ExchangeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $post = request()->all();

        if(isset($post['action'])) {


            switch($post['action'])  {

                case "sell" :

                    $order = KrakenAPIFacade::sellMarket(array($post['coin_1'], $post['coin_2']), $post['volume']);

                    if(sizeof($order['error'])>0) {
                            $data['order_error'] = $order['error'][0];
                        }
                        else {
                             $data['order_description'] = $order['result']['descr']['order'];
                             $data['order_txid'] = $order['result']['txid'][0];
                        }


                    break;

                case "buy" :

                    //Get latest price from kraken
                    $info = KrakenAPIFacade::getTicker(array($post['coin_1'], $post['coin_2'])); 
                    if((isset($info['result'])) && (is_array($info['result']))) {
                        $result = reset($info['result']);
                        $latest = $result['a'][0];
                        $volume = $post['volume'] / $latest;

                        $order = KrakenAPIFacade::buyMarket(array($post['coin_1'], $post['coin_2']), $volume);

                        if(sizeof($order['error'])>0) {
                            $data['order_error'] = $order['error'][0];
                        }
                        else {
                             $data['order_description'] = $order['result']['descr']['order'];
                             $data['order_txid'] = $order['result']['txid'][0];
                        }
                    }

                    break;
            }

        }

        //List all exchanges

        //For now just show balances on Kraken
        //$assets = KrakenAPIFacade::getAssetInfo();
        $balances = KrakenAPIFacade::getBalances();

        $data['balances'] = $balances['result'];

        return view("exchanges.show", $data);
    }

 /**
     * Show Bittrex
     *
     * @return \Illuminate\Http\Response
     */
    public function bittrex()
    {
        //
        $post = request()->all();
         if(isset($post['action'])) {


            switch($post['action'])  {

                case "sell" :

                    $ticker = Bittrex::getTicker("BTC-".$post['coin_1']);

                    $rate = $ticker['result']['Last'];

                    $order = Bittrex::sellLimit("BTC-".$post['coin_1'], $post['volume'], $rate);

                        if(!$order['success']) {
                            $data['order_error'] = $order['message'];
                        }
                        else {
                             $data['order_description'] = "Order Successful";
                             $data['order_txid'] = $order['result']['uuid'];
                        }

                    break;

                case "buy" :


                    //Get latest price from Bittrex
                     $ticker = Bittrex::getTicker("BTC-".$post['coin_1']);

                     $rate = $ticker['result']['Last'];
                     $volume = ($post['volume'] - 0.0001)/$rate;

                     $order = Bittrex::buyLimit("BTC-".$post['coin_1'], $volume, $rate);


                        if(!$order['success']) {
                            $data['order_error'] = $order['message'];
                        }
                        else {
                             $data['order_description'] = "Order Successful";
                             $data['order_txid'] = $order['result']['uuid'];
                        }

                    break;
            }

        }

        //dd(Bittrex::getMarkets());
        $balances = Bittrex::getBalances();
        foreach($balances['result'] as $balance) {
            $data['balances'][$balance['Currency']] = $balance['Balance'];
        }

        return view("exchanges.show", $data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
