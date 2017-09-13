<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


//Coins
Route::get('/coins', 'CoinController@index')->name('coins'); //view all
Route::get('/coins/create', 'CoinController@create'); // create form
Route::get('/coins/charts', 'CoinController@charts')->name('charts'); // charts
Route::get('/coins/charts/{time}', 'CoinController@charts')->name("charts24"); // charts
Route::get('/coins/{coin}/edit', 'CoinController@edit'); //edit form
Route::get('/coins/{coin}', 'CoinController@show'); //view
Route::post('/coins', 'CoinController@store'); //Submit new
Route::patch('/coins/{coin}', 'CoinController@update'); //Submit edit
Route::delete('/coins/{coin}', 'CoinController@destroy'); //Submit delete

//Transactions
Route::get('/transactions', 'TransactionController@index')->name('transactions'); //view all
Route::get('/transactions/create', 'TransactionController@create'); // create form
Route::get('/transactions/{transaction}/edit', 'TransactionController@edit'); //edit form
Route::get('/transactions/{transaction}', 'TransactionController@show'); //view
Route::post('/transactions', 'TransactionController@store'); //Submit new
Route::patch('/transactions/{transaction}', 'TransactionController@update'); //Submit edit
Route::delete('/transactions/{transaction}', 'TransactionController@destroy'); //Submit delete

//Exchanges

Route::get('/exchanges', 'ExchangeController@index')->name('exchanges'); //view all
Route::post('/exchanges', 'ExchangeController@index'); //view all

Route::get('/exchanges/kraken', 'ExchangeController@kraken')->name('kraken'); //view all
Route::post('/exchanges/kraken', 'ExchangeController@kraken'); //view all

Route::get('/exchanges/bittrex', 'ExchangeController@bittrex')->name('bittrex'); //view all
Route::post('/exchanges/bittrex', 'ExchangeController@bittrex'); //view all

Route::get('/exchanges/getprices', 'ExchangeController@getPrices');