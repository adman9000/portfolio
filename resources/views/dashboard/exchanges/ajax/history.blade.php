@extends('layouts.modal')

@section('title') 


	{{ $exchange->title }} {{ $coin->name }} History

@endsection

@section('content') 

    @if($withdrawals['success']) 

    	<h4>Withdrawal History</h4>

    	<table class='table'>

    		@foreach($withdrawals['data'] as $withdrawal) 

    			<tr><td><? var_dump($withdrawal) ?></td></tr>

    		@endforeach

    	</table>

    @endif

    @if($deposits['success']) 

    	<h4>Deposit History</h4>

    	<table class='table'>

    		@foreach($deposits['data'] as $deposit) 

    			<tr><td><? var_dump($deposit) ?></td></tr>

    		@endforeach

    	</table>

    @endif

@endsection