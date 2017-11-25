@component('mail::message')


# '{{ $type }}'' Trade Submitted for coin '{{ $coin->code }}' on scheme '{{ $scheme->title }} '

@component('mail::panel')

@if( $success )

	Submission Successful

@else

	Submission Failed

@endif

@endcomponent

Amount to buy: <?=$transaction->amount_bought?>
Amount to sell: <?=$transaction->amount_sold?>
Exchange rate: <?=$transaction->exchange_rate?>

<hr />


<?php var_dump($order) ?>



@component('mail::button', ['url' => $url])
View Scheme
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent