@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
<br />
                    <p><b>AutoTrader Plan</b></p>
                    <ol>
                    <li>Get 0.3 BTC (approx £1000) on bittrex</li>
<li>Select 50 altcoins - set a ‘buy in’ price to purchase 0.06 BTC worth (£20)</li>
<li>Sell 50% at 2x buy in price</li>
<li>Buy 50% back if drops back down to buy in price</li>
<li>Sell remainder at 5x buy in price (after 5% drop to avoid selling during increase)</li>
<li>Set new buy in price to 2x original. Repeat.</li>
</ol>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
