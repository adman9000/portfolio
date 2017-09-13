<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use adman9000\kraken\KrakenAPIFacade;
use App\Coin;
use App\CoinPrice;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

         $schedule->call(function () {

             $coins = Coin::where("code", "!=", "EUR")->get();
    foreach($coins as $coin) {
        $latest = false;
        //Get latest price from kraken (EUROS)
        if($coin->exchange == "kraken") {
            if($coin->code == "XBT") {
                $info = KrakenAPIFacade::getTicker(array($coin->code, "EUR"));

            }
            else {
                 $info = KrakenAPIFacade::getTicker(array($coin->code, "XBT"));
                }
                if((is_array($info)) && (isset($info['result']))) {
                    $result = reset($info['result']);
                    $latest = $result['a'][0];
                }
            
        }
        //Get latest price from bittrex (BITCOIN)
        else if($coin->exchange == "bittrex"){
            $info = Bittrex::getTicker("BTC-".$coin->code);
            if((is_array($info)) && (isset($info['result']))) $latest = $info['result']['Last'];
        }

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
    	$data[$price->coin_code] = new StdClass();
        $data[$price->coin_code]->price = $price->current_price;
        $data[$price->coin_code]->updated_at = $price->created_at;
        $data[$price->coin_code]->updated_at_short = $price->created_at->format('D G:i');
    }
    broadcast(new App\Events\PusherEvent(json_encode($data)));

        })->everyFiveMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
