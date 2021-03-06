<?php
/** Maybe have a separate repository file for kraken and bittrex **/

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\CoinPrice;
use App\Modules\Portfolio\Transaction;
use App\Modules\Portfolio\Scheme;
use App\Modules\Portfolio\UserExchange;
use App\Modules\Portfolio\Exchange;
use App\Modules\Portfolio\ExchangeCoin;
use App\Modules\Portfolio\UserValue;
use App\Modules\Portfolio\Alert;
use App\Modules\Portfolio\Wallet;
use App\Modules\Portfolio\WalletValue;
use App\User;
use App\Notifications\Trade;
use adman9000\kraken\KrakenAPIFacade;
use adman9000\Bittrex\Bittrex;
use Illuminate\Support\Facades\File;
use adman9000\coinmarketcap\CoinmarketcapAPIFacade;
use App\Events\PriceEvent;
use App\Notifications\PriceAlert;


class Exchanges {



    protected $current_time;

//REFACTOR BELOW FUNCTIONS



    /* runSchedule()
     * Called by cronjob every 5 minutes.
     * Runs the required functions in order to
     * 1. Save latest prices from bittrex to DB
     * 2. Run the trading rules to buy & sell as required
     * 3. Push Latest Coin prices & amounts via pusher
     * 4. Push latest BTC price & amount via pusher
     **/
    function runSchedule() {

        $this->current_time = date("Y-m-d G:i:00");

        //Get latest prices from Coinmarketcap
        $this->saveCMCPrices();

        //Update users wallet values
        $this->calculateWalletValues();
        
        //Get latest prices from exchanges
        $this->saveExchangePrices();


        //Update any incomplete orders on any exchange. TODO
        //$this->checkForCompletedOrders();

        //Run the automated trading rules. Pause this for now!
        //$this->runTradingRules();

        //Pusher events to update browsers in real time
       // $this->coinPusher();
        //$this->btcPusher();

        $this->userAlerts();

    }

    /** runNightly()
     * Runs each night
     * Downloads all orders from all exchanges for all users & stores them with values etc
     * Clears out all old prices
    **/
    function runNightly() {

        //TODO: this
        //$this->downloadOrders();
        $this->cleanupPrices();
    }

    /** Called directly by cronjob every hour. Calculates current value of each users portfolio */
    
    function calculatePortfolios() {


        $log_file = storage_path("logs/portfolios.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- calculatePortfolios() ----------------------------------"."\n");

        $users = User::with("coins", "wallets")->get();


        foreach($users as $user) {

            File::append($log_file, $user->name.": ");

            $data = array();
            $data['exchanges_btc_value'] = 0;
            $data['exchanges_gbp_value'] = 0;
            $data['exchanges_usd_value'] = 0;
            $data['wallets_btc_value'] = 0;
            $data['wallets_gbp_value'] = 0;
            $data['wallets_usd_value'] = 0;
            $data['user_id'] = $user->id;

            //Portfolio value change calculated from CMC data
            foreach($user->coins as $ucoin) {

                $ucoin->load('exchangeCoin');

                $data['exchanges_btc_value'] += $ucoin->exchangeCoin->btc_price * $ucoin->balance;
                $data['exchanges_gbp_value'] += $ucoin->exchangeCoin->gbp_price * $ucoin->balance;
                $data['exchanges_usd_value'] += $ucoin->exchangeCoin->usd_price * $ucoin->balance;

            }

            //Get the total value of all wallets
            foreach($user->wallets as $wallet) {

                $data['wallets_btc_value'] += $wallet->btc_value;
                $data['wallets_gbp_value'] += $wallet->gbp_value;
                $data['wallets_usd_value'] += $wallet->usd_value;

            }

            //totals
            $data['btc_value'] = $data['exchanges_btc_value'] + $data['wallets_btc_value'];
            $data['usd_value'] = $data['exchanges_usd_value'] + $data['wallets_usd_value'];
            $data['gbp_value'] = $data['exchanges_gbp_value'] + $data['wallets_gbp_value'];

            File::append($log_file,$data['btc_value']." BTC\n");

            UserValue::create($data);


        }


    }


    /** downloadOrders()
     * Called every night to make sure we have all order data for all users
     **/
    function downloadOrders() {

        $userExchanges = UserExchange::all();

        foreach($userExchanges as $ue) {

            echo $ue->exchange_id."<br />";

            $ue->downloadOrders();

        }

    }

    /** cleanupPrices()
     * Called every night to remove the 5 minute prices for all previous days
     **/
    function cleanupPrices() {

        DB::table('cmc_prices')->where('created_at', '<', date("Y-m-d G:i:s", strtotime("24 hours ago")))->where(DB::raw("DATE_FORMAT(created_at, '%i-%S')"), "!=", "00-00")->delete();
        DB::table('exchanges_prices')->where('created_at', '<', date("Y-m-d G:i:s", strtotime("24 hours ago")))->where(DB::raw("DATE_FORMAT(created_at, '%i-%S')"), "!=", "00-00")->delete();

    }

    /** calculateWalletValues()
     * Called every 5 minutes, updates values of users wallets
     **/
    function calculateWalletValues() {

        //Get all users with their wallets
        $users = User::with("wallets")->get();


        foreach($users as $user) {

            foreach($user->wallets as $wallet) {

                //Get latest coin price data
                $coin = Coin::with("latestCoinprice")->find($wallet->coin_id);

                if($coin->latestCoinprice) {

                    //create the data array for the wallet value record
                    $data = [
                        'user_id' => $user->id,
                        'coin_id' => $coin->id,
                        'wallet_id' => $wallet->id,
                        'balance' => $wallet->balance,
                        'btc_price' => $coin->latestCoinprice->btc_price,
                        'usd_price' => $coin->latestCoinprice->usd_price,
                        'gbp_price' => $coin->latestCoinprice->gbp_price,
                        'btc_value' => $wallet->balance * $coin->latestCoinprice->btc_price,
                        'usd_value' => $wallet->balance * $coin->latestCoinprice->usd_price,
                        'gbp_value' => $wallet->balance * $coin->latestCoinprice->gbp_price
                    ];

                    //Create the value record for a permanent snapshot
                    WalletValue::create($data);

                    //update the wallet record
                    unset($data['wallet_id']);
                    unset($data['btc_price']);
                    unset($data['usd_price']);
                    unset($data['gbp_price']);
                    $wallet->update($data);
                }

            }
        }
    }


    /** getBittrexPrices
    ** called every 5 mins, saves CMC prices to DB
    **/
    function saveCMCPrices() {

        $time = $this->current_time;

        $log_file = storage_path("logs/cmc.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- saveCMCPrices() ----------------------------------"."\n");

        //Get latest markets for everythign on CMC
        $markets = CoinmarketcapAPIFacade::getTickers(0, 0, "GBP");

        //Get an array of all coins in my DB
        $coins = Coin::all();

        //Loop through markets, find any of my coins and save the latest price to DB
        //Also make sure we have got all of the current top 100 coins in the DB
        $i=0;
        foreach($markets as $market) {
            $i++;
            $price_added = false;

            if(!$market['price_btc']) $market['price_btc'] = 0;
            if(!$market['price_usd']) $market['price_usd'] = 0;
            if(!$market['price_gbp']) $market['price_gbp'] = 0;

            $base = $market['symbol'];
            foreach($coins as $coin) {
                if($coin->code == $base) {
                    //Found the coin in our database, so add the latest price record
                    $price_info = array("created_at" => $time,"coin_id"=>$coin->id, "btc_price"=>$market['price_btc'], "usd_price"=>$market['price_usd'], "gbp_price"=>$market['price_gbp'], "current_supply"=>$market['total_supply']);
                   
                    $price = CoinPrice::create($price_info);
                    File::append($log_file, "Price saved for ".$coin->code."\n");
                    $price_added = true;
                }
            }

            //If this coin is not already in our database add it
            if(!$price_added) {
                    
                $coin_info = array("code"=>$base, "name"=>$market['name'], "max_supply"=>$market['max_supply']);
                $coin = Coin::create($coin_info);

                //Also add the latest price record
                $price_info = array("created_at" => $time,"coin_id"=>$coin->id, "btc_price"=>$market['price_btc'], "usd_price"=>$market['price_usd'], "gbp_price"=>$market['price_gbp'], "current_supply"=>$market['total_supply']);
                $price = CoinPrice::create($price_info);
                File::append($log_file, "Coin added ".$coin->code."\n");
            }
        }

        //Get all coins again with latest price for each, and update the coin record
        $coins = Coin::with("latestCoinprice")->get();

        foreach($coins as $coin) {
            $prices = array();
            if($coin->latestCoinprice) {
                $prices['latest']['btc'] = $coin->latestCoinprice->btc_price;
                $prices['latest']['usd'] = $coin->latestCoinprice->usd_price;
                $prices['latest']['gbp'] = $coin->latestCoinprice->gbp_price;
                $coin->prices = json_encode($prices);
                $coin->save();
            }
            else 
                File::append($log_file, "No latest price found for ".$coin->code."\n");
        }


        //Finally we can update users wallets
        $wallets = Wallet::all();

        foreach($wallets as $wallet) {


            $coin = Coin::with('latestCoinprice')->find($wallet->coin_id);
            
            if(($coin) && ($coin->latestCoinprice)){
                $btc_price = $coin->latestCoinprice['btc_price'];
                $usd_price = $coin->latestCoinprice['usd_price'];
                $gbp_price = $coin->latestCoinprice['gbp_price'];

                $wallet->btc_value = $wallet->balance * $btc_price;
                $wallet->usd_value = $wallet->balance * $usd_price;
                $wallet->gbp_value = $wallet->balance * $gbp_price;
            

                $wallet->save();
            }

        }


        File::append($log_file, "---------------------------- saveCMCPrices() complete -----------------------------"."\n\n");
    }


    /** saveExchangePrices
    ** called every 5 mins, saves exchange prices to DB
    **/
    function saveExchangePrices() {


        $log_file = storage_path("logs/exchanges.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- saveExchangePrices() ----------------------------------"."\n");

        //Get all exchanges in the database
        $exchanges = Exchange::all()->sortByDesc('id');

        //Loop through them and get the latest prices from each
        foreach($exchanges as $myexchange) {
           // $myexchange->setupCoins();
            $myexchange->retrievePrices($this->current_time);

            File::append($log_file, "Prices saved for ".$myexchange->title."\n");
       


            //Update the exchange coins records
            $coins = ExchangeCoin::with("latestCoinprice")->where("exchange_id", $myexchange->id)->get();


            foreach($coins as $coin) {
                if($coin->latestCoinprice) {
                    //Pivot, so update with a query
                    DB::table('coin_exchange')->where('id', $coin->id)
                    ->update(['btc_price' => $coin->latestCoinprice->btc_price,
                    'usd_price' => $coin->latestCoinprice->usd_price,
                    'gbp_price' => $coin->latestCoinprice->gbp_price,
                    'updated_at' => date("Y-m-d G:i:s")
                    ]);

                    //Update all users coin values for this coin
                    DB::table('coin_user')->where('exchange_coin_id', $coin->id)
                    ->update(['gbp_value' => DB::raw($coin->latestCoinprice->gbp_price ." * `balance`"),
                    'updated_at' => date("Y-m-d G:i:s")
                    ]);
                   
                }
            }

            File::append($log_file, "Coins saved for ".$myexchange->title."\n");

        }

        File::append($log_file, "---------------------------- saveExchangePrices() complete -----------------------------"."\n");

    }

    /* userAlerts()
     * Send alerts to users if targets met
    **/
    function userAlerts() {

        $alerts = Alert::where("triggered", false)->get();

        foreach($alerts as $alert) {

            //reset coin value
            $coin_value = 0;

            //Get all records of this coin for this user
            $alert->user->load('coins');
            foreach($alert->user->coins as $ucoin) {
                if($ucoin->coin_id == $alert->coin_id)
                    $coin_value += $ucoin->gbp_value;
            }
            $alert->user->load('wallets');
            foreach($alert->user->wallets as $wallet) {
                if($wallet->coin_id == $alert->coin_id)
                    $coin_value += $wallet->gbp_value;
            }

            $coin_price = $alert->coin->latestCoinprice->gbp_price;

            if( ($alert->gbp_max_value>0) && ($coin_value >= $alert->gbp_max_value)) {

                //Value greater than
                $alert->user->notify(new PriceAlert($alert->coin->code." value above £".$alert->gbp_max_value));

                //mark as triggered
                Alert::where("id", $alert->id)->update(["triggered" => 1]);
            }
            else if(($alert->gbp_min_value>0) && ($coin_value <= $alert->gbp_min_value)) {
                
                 //Value less than
                $alert->user->notify(new PriceAlert($alert->coin->code." value below £".$alert->gbp_max_value));

                //mark as triggered
                Alert::where("id", $alert->id)->update(["triggered" => 1]);
            }

            else if(($alert->gbp_max_price>0) && ($coin_price >= $alert->gbp_max_price)) {

                //Value greater than
                $alert->user->notify(new PriceAlert($alert->coin->code." price above £".$alert->gbp_max_price));

                //mark as triggered
                Alert::where("id", $alert->id)->update(["triggered" => 1]);
            }
            else if(($alert->gbp_min_price>0) && ($coin_price <= $alert->gbp_min_price)) {
                
                 //Value less than
                $alert->user->notify(new PriceAlert($alert->coin->code." price below £".$alert->gbp_min_price));

                //mark as triggered
                Alert::where("id", $alert->id)->update(["triggered" => 1]);
            }

        }
    }

    /* oneoff function to set up exchange coins table **/
    function setupCoins() {

         //Get all exchanges in the database
        $exchanges = Exchange::all();

        //Loop through them and get the latest prices from each
        foreach($exchanges as $myexchange) {
            $myexchange->setupCoins();
        }

    }



    /** btcPusher
    * TODO: store BTC rate & amount in DB?
     * Push BTC info
     */
    function btcPusher() {

        $data = array();

        //Get BTC balance & exchange rate from bittrex
        $btc_balance = Bittrex::getBalance("BTC");
        $btc_market = Bittrex::getMarketSummary("USDT-BTC");
        $data['btc_additional_amount'] = $btc_balance['result']['Balance'];
        $data['btc_usd_rate'] = $btc_market['result'][0]['Last'];

        //Pusher
        broadcast(new \App\Events\PusherEvent(json_encode($data), "portfolio\\btc"));
    }

      /**coinPusher
    * Called from /coins page to update it on load
    * And every 5 minutes to update the page after new prices obtained & trades carried out
    **/
    function coinPusher() {

        $log_file = storage_path("logs/bittrex.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- coinPusher() ----------------------------------"."\n");

        //Get an array of all coins in my DB
       // $coins = Coin::all();

        //Loop through all live schemes and broadcast current prices and amounts of all coins held
        $schemes = Scheme::where("enabled", true)->get();

        foreach($schemes as $scheme) {


            File::append($log_file, "\nScheme pushing: ".$scheme->title."\n");

            //Add extra info to coins
            $data = array();

            foreach($scheme->coins as $c=>$coin) {
                $datum = array();
                $datum['id'] = $coin->id;
                $datum['code'] = $coin->code;
                $datum['name'] = $coin->name;
                $datum['current_price'] = round($coin->latestCoinPrice->current_price, 7);
                $datum['current_value'] = round($coin->pivot->amount_held * $coin->latestCoinPrice->current_price, 7);
                $datum['diff'] = round((($coin->latestCoinPrice->current_price / $coin->pivot->set_price) * 100) - 100, 2);
                $datum['set_price'] = round($coin->pivot->set_price, 7);
                $datum['been_bought'] = $coin->pivot->been_bought;
                $datum['sale_completed_1'] = $coin->pivot->sale_completed_1;
                $data[] = $datum;
            }

            broadcast(new \App\Events\PusherEvent(json_encode($data), "portfolio\\prices\\".$scheme->id));
        }

    }



    /**updateCoinBalances
     * Save current bittrex balances to DB
     * Not needed that often? Trade function does this anyway
    **/
    function updateCoinBalances() {
    
        //Get balances of my coins according to Bittrex
        $balances = Bittrex::getBalances();

        //Get an array of all coins in my DB
        $coins = Coin::all();

        //For pusher event
        $latest_prices = array();

        //Put balances into coins array and save balance to DB
        $my_balances = array();
        foreach($balances['result'] as $balance) {
            foreach($coins as $c=>$coin) {
                if($coin->code == $balance['Currency']) {
                    $coins[$c]->amount_owned= $balance['Balance'];
                    $coins[$c]->save();
                }
            }
        }
    }


  

    //reset coins to current price, no triggers, only used on setup
    function resetCoins() {

        Coin::where('id', '>', 0)->update([
            'been_bought' => 0,
            'sale_completed_1' => 0,
            'sale_completed_2' => 0,
            'sale_trigger_1' => 0,
            'sale_trigger_2' => 0,
            'highest_price' => 0
            ]);

        $coins = Coin::with('latestCoinprice')->get();
        foreach($coins as $coin) {
            $coin->buy_point = $coin->latestCoinprice->current_price;
            $coin->highest_price = $coin->latestCoinprice->current_price;
            $coin->save();
        }
    }


    /*  runTradingRules
     * Important function! Does the buying & selling
    * Runs every 5 minutes after we have got latest prices
    */
    function runTradingRules() {

        $log_file = storage_path("logs/bittrex.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- runTradingRules() ----------------------------------"."\n");

        if(!env("AUTOTRADE_ENABLED")) {

            File::append($log_file, "Autotrade disabled\n"."\n");
            return false;
        }

        //Get any existing orders first so we dont duplicate - TODO: Cancel existing orders if not filled?
        $orders = Bittrex::getOpenOrders();
        foreach($orders['result'] as $order) {
            $arr = explode("-", $order['Exchange']);
            $existing_orders[] = $arr[1];
        }

        //Get all DB coins with latest prices
       // $coins = Coin::with('latestCoinprice')->get();


       // $balances = Bittrex::getBalances();

        //Get all enabled schemes
        $schemes = scheme::where("enabled", true)->get();

        foreach($schemes as $scheme) {

            File::append($log_file, "\nScheme Running: ".$scheme->title."\n");

            foreach($scheme->coins as $coin) {

                File::append($log_file, "\n".$coin->code."\n");

                //Probably a better way to do this with relationships??
                $mycoin = Coin::with('latestCoinprice')->find($coin->id);
                $latest_price = $mycoin->latestCoinprice;

                $baseline_price = $coin->pivot->set_price;

                $next_baseline_price = $scheme->price_increase_percent ?  $baseline_price * $scheme->price_increase_percent/100 : $baseline_price;

                $current_price = $latest_price->current_price;

                $amount_held = $coin->pivot->amount_held;

                $highest_price = max($current_price, $coin->pivot->highest_price);

                $buy_point = $baseline_price - ($baseline_price*$scheme->buy_drop_percent/100);

                $btc_buy_amount = $scheme->buy_amount;

                $coin_buy_amount = $scheme->buy_amount / $current_price;

                $sale_1_trigger = $baseline_price + ($baseline_price*$scheme->sell_1_gain_percent/100);

                $sale_1_point = $scheme->sell_1_drop_percent==0 ? false : $highest_price - ($highest_price*$scheme->sell_1_drop_percent/100);

                $sale_2_trigger = $baseline_price + ($baseline_price*$scheme->sell_2_gain_percent/100);

                $sale_2_point = $scheme->sell_2_drop_percent==0 ? false : $highest_price - ($highest_price*$scheme->sell_2_drop_percent/100);

                $sale_1_amount = $amount_held*$scheme->sell_1_sell_percent/100;

                $sale_2_amount = $amount_held*$scheme->sell_2_sell_percent/100;

                File::append($log_file, "Baseline Price: ".$baseline_price."\n");
                File::append($log_file, "Next Baseline Price: ".$next_baseline_price."\n");
                File::append($log_file, "Current Price: ".$current_price."\n");
                File::append($log_file, "High Price: ".$highest_price."\n");
                File::append($log_file, "Buy Point: ".$buy_point."\n");
                File::append($log_file, "Coin Buy Amount: ".$coin_buy_amount."\n");
                File::append($log_file, "Sale 1 Trigger: ".$sale_1_trigger."\n");
                File::append($log_file, "Sale 1 Price: ".$sale_1_point."\n");
                File::append($log_file, "Sale 2 Trigger: ".$sale_2_trigger."\n");
                File::append($log_file, "Sale 2 Price: ".$sale_2_point."\n");
                File::append($log_file, "Amount Held: ".$amount_held."\n");
                File::append($log_file, "Sale 1 Amount: ".$sale_1_amount."\n");
                File::append($log_file, "Sale 2 Amount: ".$sale_2_amount."\n");

                if($coin->pivot->been_bought) {
                    
                    //We own some of this coin in this scheme, so deal with it!
                   
                    File::append($log_file, "Testing Sell Points:" .$coin->pivot->sale_1_completed."\n");

                    if($coin->pivot->sale_1_completed==0) {

                        File::append($log_file, "Sale 1 not completed"."\n");

                        //Handle sale 1 level
                        if($current_price >= $sale_1_trigger) {

                            //Current price has reached trigger point 1
                            $coin->pivot->sale_1_triggered = true;

                            File::append($log_file, "Sale 1 triggered"."\n");

                        }

                        if($coin->pivot->sale_1_triggered) {

                            File::append($log_file, "Sale 1 already triggered"."\n");

                            //We have reached the trigger point for sale 1. Sell once we reach the sell point

                            if(!($coin->pivot->sale_1_completed) && ((!$sale_1_point) || ($current_price <= $sale_1_point))) {

                                //SELL SELL SELL!
                                File::append($log_file, "SALE 1 - SELLING ".$sale_1_amount."\n");

                                 if($this->bittrexSell($coin, $sale_1_amount, $current_price, $scheme)) {

                                    //TODO: Move all this to the order checking function so we only mark coins as bought etc when order has been confirmed?
                                    //Set sale_completed=1
                                    $coin->pivot->sale_1_completed = true;
                                    $coin->pivot->amount_held -= $sale_1_amount;

                                    File::append($log_file, "Sale successful"."\n");

                                    //If sale 1 is for 100%, reset triggers
                                    if($coin->pivot->amount_held == 0) {
                                        //Reset triggers, update baseline price
                                        $coin->pivot->been_bought = false;
                                        $coin->pivot->sale_1_triggered = false;
                                        $coin->pivot->sale_1_completed = false;
                                        $coin->pivot->sale_2_triggered = false;
                                        $coin->pivot->set_price = $next_baseline_price;
                                        $coin->pivot->amount_held -= $sale_2_amount;
                                        $highest_price = 0;

                                        File::append($log_file, "Sold out. Triggers reset"."\n");

                                    }


                                }


                            }

                            //Current price not yet dropped enough to sell

                        }

                    }
                    
                    if(!$coin->pivot->sale_2_completed) {

                        File::append($log_file, "Sale 2 not completed"."\n");

                        //Handle sale 2 level
                        if($current_price >= $sale_2_trigger) {

                            $coin->pivot->sale_2_triggered = true;

                            File::append($log_file, "Sale 2 triggered"."\n");

                        }

                        if($coin->pivot->sale_2_triggered) {

                            //We have reached the trigger point for sale 1. Sell once we reach the sell point

                            if((!$sale_2_point) || ($current_price <= $sale_2_point)) {

                                //SELL SELL SELL!

                                File::append($log_file, "SALE 2 - SELLING ".$sale_2_amount);

                                 if($this->bittrexSell($coin, $sale_2_amount, $current_price, $scheme)) {

                                    //Reset triggers, update baseline price
                                    $coin->pivot->been_bought = false;
                                    $coin->pivot->sale_1_triggered = false;
                                    $coin->pivot->sale_1_completed = false;
                                    $coin->pivot->sale_2_triggered = false;
                                    $coin->pivot->set_price = $next_baseline_price;
                                    $coin->pivot->amount_held -= $sale_2_amount;
                                    $highest_price = 0;

                                    File::append($log_file, "Sale successful"."\n");

                                }

                            }

                            //Current price not yet dropped enough to sell

                        }
                    }
                }

                else {

                    //Handle buying the coin

                    File::append($log_file, "Testing Buy Point"."\n");

                    if($current_price <= $buy_point) {

                        //TODO: Add trigger percent to avoid buying during crash

                        //Buy it
                        File::append($log_file, "BUYING ".$coin_buy_amount." with ".$btc_buy_amount." BTC"."\n");

                        if( $volume = $this->bittrexBuy($coin, $btc_buy_amount, $current_price, $scheme)) {

                            //St the bought flag
                            $coin->pivot->been_bought = 1;

                            $coin->pivot->amount_held += $volume;
                            
                            File::append($log_file, "Buy successful"."\n");

                        }

                    }

                }


                 File::append($log_file, "Amount Held: ".$coin->pivot->amount_held."\n");
                //Save the coin_scheme data
                $coin->pivot->highest_price = $highest_price;
                $coin->pivot->save();

            } //end foreach coin
 


        }

        
    }


    /** checkIncompleteOrders
    * Check incomplete transactions against orders in bittrex to mark the transactions as complete
    */
    function checkForCompletedOrders() {
     //Get all incomplete transactions and check against orders on bittrex to see if they have been completed
        $transactions = Transaction::where("status", "unconfirmed")->where('uuid', '!=', '')->get();
        foreach($transactions as $transaction) {
            $order = Bittrex::getOrder($transaction->uuid);
            if($order['result']['Closed'] == true) {
                $transaction->amount_bought = $order['result']['Quantity'];
                $transaction->amount_sold = $order['result']['Price'];
                $transaction->exchange_rate = $order['result']['PricePerUnit'];
                $transaction->fees = $order['result']['CommissionPaid'];
                $transaction->status='confirmed';
                $transaction->save();
            }
        }
    }



    /** Buy & Sell functions
     * Called from the Trade function when required
    **/

    /** bittrexSell()
     * @param $coin - the coin being bought
     * @param $volume - the amount of that coin being sold (for BTC)
     * @param $rate - the limiting rate of exchange
     **/
    function bittrexSell($coin, $volume, $rate, $scheme=false) {

        if($scheme) $scheme_id = $scheme->id;

        if(env("AUTOTRADE_ENABLED") == "test") {
            $order['success'] =  true;//TESTING
            $order['result']['uuid'] = "TEST0001";
            //return true;
        }
        else {
            //Place order
            $order = Bittrex::sellLimit("BTC-".$coin->code, $volume, $rate);
        }

        if(!$order['success']) {
            //Order failed, alert me somehow


        $user = User::find(1);
        $user->notify(new Trade(array("type" => "sell", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => array(), "success" => false)));

            return false;
        }
        else {

             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_bought_id" => 0,
                "coin_sold_id" => $coin->id,
                "amount_sold" => $volume,
                "amount_bought" => $volume*$rate,
                "exchange_rate" => $rate,
                'fees' => 0,
                'user_id' => 1,
                "status" => "unconfirmed",
                "uuid" => $order['result']['uuid'],
                'scheme_id' => $scheme_id
                );
            $transaction = Transaction::create($transaction_info);


        $user = User::find(1);
        $user->notify(new Trade(array("type" => "sell", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => $transaction, "success" => true)));

            return $volume;
        }
    }

    /** bittrexBuy()
     * @param $coin - the coin being bought
     * @param $amount - the amount of BTC being spent
     * @param $rate - the limiting rate of exchange
     **/
    function bittrexBuy($coin, $amount, $rate, $scheme=false) {
        
        if($scheme) $scheme_id = $scheme->id;

        $log_file = storage_path("logs/bittrex.log");
         //Calculate volume being bought from the amount of BTC being sold, the rate, and bittrex fees (0.25%)
        $volume = ($amount - ($amount*0.0025))/$rate; 

			File::append($log_file, "AUTOTRADE_ENABLED: ".env("AUTOTRADE_ENABLED"));
			
         if(env("AUTOTRADE_ENABLED") == "test") {
            $order['success'] =  true;//TESTING
            $order['result']['uuid'] = "TEST0001";
        }
        else {
            
            //Place order
            $order = Bittrex::buyLimit("BTC-".$coin->code, $volume, $rate);
        }

			File::append($log_file, json_encode($order));
			
        if(!$order['success']) {
            //Order failed, alert me somehow
	
            $user = User::find(1);
            $user->notify(new Trade(array("type" => "buy", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => array(), "success" => false)));

            return false;
        }
        else {
             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_sold_id" => 0,
                "coin_bought_id" => $coin->id,
                "amount_sold" => $amount,
                "amount_bought" => $volume,
                "exchange_rate" => $rate,
                'fees' => 0,
                'user_id' => 1,
                "status" => "unconfirmed",
                "uuid" => $order['result']['uuid'],
                'scheme_id' => $scheme_id
                );
            $transaction = Transaction::create($transaction_info);


            $user = User::find(1);
            $user->notify(new Trade(array("type" => "buy", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => $transaction, "success" => true)));
        
            return $volume;
        }
    }






    /********* V1 ***/
	 /** getPrices
    }
    * Get latest prices for all my coins from relevant exchanges
    **/

	public function getPrices() {

		//First of all we can skip Euros (might take this out of coins list entirely)

        //Secondly, bitcoins are handled differently. All other coins are priced in bitcoin at whichever exchange they were bought from, bitcoin is priced in Euro based on Kraken exchange

        $coins = Coin::where("code", "!=", "EUR")->where("code", "!=", "XBT")->get();

        $xbt = Coin::where("code", "=", "XBT")->first();

        //Create the array of asset pairs to pass to the kraken ticker
        $kraken_pairs = array();
        $bittrex_coins = array();

        //Preset first kraken pair to XBTEUR
        $kraken_pairs[] = "XBTEUR";
        foreach($coins as $coin) {
            if($coin->exchange == "kraken") $kraken_pairs[] = $coin->code."XBT";
            else if($coin->exchange == "bittrex") $bittrex_coins[] = $coin;
        }

        //KRAKEN VERSION - Gets all tickers with one call

        //Get Ticker prices
        $ticker = KrakenAPIFacade::getTickers($kraken_pairs);

        if(isset($ticker['result'])) {

            //Save the bitcoin one
            if(isset($ticker['result']['XXBTZEUR'])) {
                $price = new CoinPrice();
                $price->coin_id = $coin->id;
                $price->current_price = $ticker['result']['XXBTZEUR']['a'][0];
                $price->save();
                $price->coin_code = "XBT";
                $latest_prices[] = $price;
                unset($ticker['result']['XXBTZEUR']);
            }

            //Figure out the rest, kraken has weird codes!
            foreach($ticker['result'] as $pair=>$values) {
                if(substr($pair, strlen($pair)-4, 4) == "XXBT") $code = substr($pair, 1, 3);
                else $code = substr($pair, 0, 4);

                //Get the coin id and save the price
                $coin = Coin::where("code", "=", $code)->first();
                $price = new CoinPrice();
                $price->coin_id = $coin->id;
                $price->current_price = $values['a'][0];
                $price->save();
                $price->coin_code = $coin->code;
                $latest_prices[] = $price;
            }
        }

        //BITTREX VERSION - does tickers singly

            //Get latest price from bittrex (BITCOIN)
            foreach($bittrex_coins as $coin) {
                $info = Bittrex::getTicker("BTC-".$coin->code);
                if((is_array($info)) && (isset($info['result']))) $latest = $info['result']['Last'];
            
                //Save the price
                if($latest) {
                    $price = new CoinPrice();
                    $price->coin_id = $coin->id;
                    $price->current_price = $latest;
                    $price->save();
                    $price->coin_code = $coin->code;
                    $latest_prices[] = $price;
                }
            }


        //send pusher event informing of latest coin prices
        $data = array();
        foreach($latest_prices as $price) {
            $data[$price->coin_code] = array();
            $data[$price->coin_code]['price'] = $price->current_price;
            $data[$price->coin_code]['updated_at'] = $price->created_at;
            $data[$price->coin_code]['updated_at_short'] = $price->created_at->format('D G:i');
        }
        broadcast(new \App\Events\PusherEvent(json_encode($data)));

    }

}