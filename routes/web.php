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
Route::get('/coins/{coin}/edit', 'CoinController@edit'); //edit form
Route::get('/coins/{coin}', 'CoinController@show'); //view
Route::post('/coins', 'CoinController@store'); //Submit new
Route::patch('/coins/{coin}', 'CoinController@update'); //Submit edit
Route::delete('/coins/{coin}', 'CoinController@destroy'); //Submit delete