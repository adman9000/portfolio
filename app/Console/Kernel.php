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

         $schedule->call('\App\Repositories\Exchanges@calculatePortfolios')->hourlyAt(44);

         $schedule->call('\App\Repositories\Exchanges@runSchedule')->everyFiveMinutes();


         $schedule->call('\App\Repositories\Exchanges@runNightly')->dailyAt('12:17');

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
