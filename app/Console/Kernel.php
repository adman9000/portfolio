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
                $result = reset($info['result']);
                $latest = $result['a'][0];

                $price = new CoinPrice();
                $price->coin_id = $coin->id;
                $price->current_price = $latest;
                $price->save();
            }


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
