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
use App\Coin;
use adman9000\kraken\KrakenAPIFacade;
use App\CoinPrice;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/pusher', function() {
   
    $coins = Coin::where("code", "!=", "EUR")->get();
    foreach($coins as $coin) {
        //Get latest price from kraken
        $info = KrakenAPIFacade::getTicker(array($coin->code, "EUR"));
        $result = reset($info['result']);
        $latest = $result['a'][0];

        $price = new CoinPrice();
        $price->coin_id = $coin->id;
        $price->current_price = $latest;
        $price->save();
        $price->coin_code = $coin->code;
        $latest_prices[] = $price;
    }

    //send pusher event informing of latest coin prices
    $data = array();
    foreach($latest_prices as $price) {
    	$data[$price->coin_code] = new StdClass();
        $data[$price->coin_code]->price = $price->current_price;
        $data[$price->coin_code]->updated_at = $price->created_at;
        $data[$price->coin_code]->updated_at_short = $price->created_at->format('D G:i');
    }
    broadcast(new App\Events\PusherEvent(json_encode($data)));

    return "Event has been sent!";
});

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