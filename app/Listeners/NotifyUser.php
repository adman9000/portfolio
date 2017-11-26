<?php

namespace App\Listeners;

use App\Events\PriceEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use App\Notifications\PriceAlert;

class NotifyUser
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PriceEvent  $event
     * @return void
     */
    public function handle(PriceEvent $event)
    {
        //

        //Testing slack notifications - it WORKS!!
        $user = User::find(1);
        $portfolioValue = $user->portfolioValue();
        $user->notify(new PriceAlert($portfolioValue->gbp_value));
    }
}
