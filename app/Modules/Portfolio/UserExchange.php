<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;
use App\User;

use adman9000\cryptoexchange\CryptoExchange;

class UserExchange extends Model
{
    //
    protected $guarded = [];


    protected $table = "users_exchanges";



 	//relationships
    public function user() {
    	return $this->belongsTo('App\Modules\Portfolio\User');
    }

    public function exchange() {
        return $this->belongsTo('App\Modules\Portfolio\Exchange');
    }


    function getExchangeAPI() {

        $exchange = $this->exchange->getExchangeAPI($this->api_key, $this->api_secret);

        return $exchange;
    }

    function getExchangeClass() {

        $exchange = $this->exchange->getExchangeClass();
        $exchange->setAPIKey($this->api_key, $this->api_secret);
        return $exchange;
    }

    /**
     * getAccountStats() - TODO
     * @param exchange name
     * @return array of stats for users account on given exchange (or all if no params passed)
    **/
    public function getAccountStats() {

        return false;
         
    }


    /** 
     * getBalances()
     * @return coin balances for the given exchange in consistent format
    **/
    public function getBalances($inc_zero=true) {

        //Must be an exchange selected
        if(!$class = $this->getExchangeAPI()) return false;
        else {
            return $class->getBalances();
        }

    }

    /** updateBalances()
     * Update the balances in the DB for all coins held by this user on this exchange
     **/
    public function updateBalances() {

        $result = $this->getBalances(true);

        if(!$result['success'])  {
            var_dump($result['errors']);
            return false;
        }

/**
        //BTC is done different
        $coin = ExchangeCoin::where('code',"BTC")->where('exchange_id', $this->exchange_id)->get()->first();

        if($balances['BTC']['balance']>0) {
            if($coin) {
                $ucoin = UserCoin::updateOrCreate(
                   [ 'exchange_coin_id'=>$coin->id, 'user_id'=>$this->user_id], 
                    ['coin_id' => $coin->coin_id, 'user_exchange_id'=> $this->id, 'balance' => $balances['btc']['balance'], 'available' => $balances['btc']['available'], 'locked' => $balances['btc']['locked']]);
            }
        }
        else {
            //No BTC, delete existing BTC balance record
            UserCoin::where('user_id', $this->user_id)->where('exchange_coin_id', $coin->id)->delete();
        }
**/
        //Loop through the balances at the exchange and make sure they are stored in the DB
        foreach($result['data'] as $asset) {

            $coin = ExchangeCoin::where('code',$asset['code'])->where('exchange_id', $this->exchange_id)->get()->first();

            if($coin) {

                if($asset['balance'] == 0) {
                    UserCoin::where('user_id', $this->user_id)->where('exchange_coin_id', $coin->id)->delete();
                }
                else {
                    
                    $ucoin = UserCoin::updateOrCreate(
                       [ 'exchange_coin_id'=>$coin->id, 'user_id'=>$this->user_id], 
                        ['coin_id' => $coin->coin_id, 'user_exchange_id'=> $this->id, 'balance' => $asset['balance'], 'available' => $asset['available'], 'locked' => $asset['locked']]);
                }
            }
            else {
                //Their coin is not in our database
            }


        }
    }

    /** downloadOrders()
     * Download all of this users orders from this exchange to make sure we have the most up to date info
     * Runs nightly, can also be run manually via button on exchange page
     **/
    function downloadOrders() {

        $class = $this->getExchangeClass();
        if($class) return $class->getOrders();
        else return false;

    }


    /** 
     * getAssetAddress
     * @param $symbol
     * @return $address
    **/
    function getAssetAddress($symbol) {

        $exchange_api = $this->getExchangeAPI();

        $result = $exchange_api->depositAddress($symbol);

        return $result['data']['address'];
         
    }

    function getWithdrawalHistory($symbol) {

        $exchange_api = $this->getExchangeAPI();

        $result = $exchange_api->withdrawalHistory($symbol);

        return $result;
    }

    function getDepositHistory($symbol) {

        $exchange_api = $this->getExchangeAPI();

        $result = $exchange_api->depositHistory($symbol);

        return $result;
    }
}

   