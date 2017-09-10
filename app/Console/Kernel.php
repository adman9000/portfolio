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

            $coins = Coin::all();
            foreach($coins as $coin) {
                //Get latest price from kraken
                $info = KrakenAPIFacade::getTicker(array($coin->code, "EUR"));

                if($info['result']) {
                    $result = reset($info['result']);
                    $latest = $result['a'][0];

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
                $data[$price->coin_code]->price = $price->current_price;
                $data[$price->coin_code]->updated_at = $price->created_at;
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
