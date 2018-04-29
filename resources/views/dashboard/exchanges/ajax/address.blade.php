@extends('layouts.modal')

@section('title') 


	{{ $exchange->title }} {{ $coin->name }} Deposit Address

@endsection

@section('content') 


	<h3> {{ $address }} </h3>

@endsection