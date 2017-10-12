@component('mail::message')


# '{{ $type }}'' Trade Submitted for coin '{{ $coin->code }}' on scheme '{{ $scheme->title }} '

@if( $success )

	<p>Submission Successful</p>

@else

	<p>Submission Failed</p>

@endif

<?php var_dump($order) ?>

<hr />

<?php var_dump($transaction) ?>


@component('mail::button', ['url' => $url])
View Scheme
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent