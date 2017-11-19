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

use App\Repositories\Exchanges;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


Auth::routes();

Route::get("/test", function() { 

	//Role::create(['name' => 'administrator']);
	//Role::create(['name' => 'member']);
	Permission::create(['name' => 'edit users']);

         $user = Auth::user();

         //$user->assignRole('member', 'administrator');
         $user->givePermissionTo('edit users');

	echo "OK";
	dd($user->hasRole("member"));
	die();

});

//ADMIN ROUTES
Route::get("/admin", "Admin\AdminController@index")->name("admin");

Route::prefix('admin')->group(function() {


	Route::any("/home", "Admin\AdminController@run");

	Route::get("/users", "Admin\UserController@index")->name('users');
	Route::any("/users/{view?}/{user?}", "Admin\UserController@run");

	Route::get("/content", "Admin\ContentController@index")->name('content');
	Route::any("/content/{view?}/{content?}", "Admin\ContentController@run");
});


Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@index')->name('home');


//Coins
Route::get('/coins', 'CoinController@index')->name('coins'); //view all
Route::get('/coins/create', 'CoinController@create'); // create form
Route::get('/coins/charts', 'CoinController@charts')->name('charts'); // charts
Route::get('/coins/charts/{time}', 'CoinController@charts')->name("charts24"); // charts
Route::get('/coins/{coin}/edit', 'CoinController@edit'); //edit form
Route::get('/coins/{coin}', 'CoinController@show'); //view
Route::post('/coins/tobtc/{coin}', 'CoinController@tobtc'); // convert to btc
Route::get('/coins/tobtc/{coin}', 'CoinController@tobtc'); // convert to btc
Route::post('/coins', 'CoinController@store'); //Submit new
Route::patch('/coins/{coin}', 'CoinController@update'); //Submit edit
Route::delete('/coins/{coin}', 'CoinController@destroy'); //Submit delete

//Schemes
Route::get('/schemes', 'SchemeController@index')->name('schemes'); //view all
Route::get('/schemes/create', 'SchemeController@create'); // create form
Route::get('/schemes/{scheme}/edit', 'SchemeController@edit'); //edit form
Route::get('/schemes/{scheme}/coins', 'SchemeController@coins'); //edit form
Route::get('/schemes/{scheme}/orders', 'SchemeController@orders'); //view orders/transaction records for this scheme
Route::get('/schemes/{scheme}', 'SchemeController@show'); //view
Route::post('/schemes', 'SchemeController@store'); //Submit new
Route::patch('/schemes/{scheme}', 'SchemeController@update'); //Submit edit
Route::patch('/schemes/{scheme}/coins', 'SchemeController@setCoins'); //Set Coins
Route::delete('/schemes/{scheme}', 'SchemeController@destroy'); //Submit delete
Route::patch('/schemes/{scheme}/enable', 'SchemeController@enable'); //Submit enable.disable form

Route::get('/schemes/{scheme}/ajax/{view}/{id}', 'SchemeController@ajaxView'); //All Ajax views
Route::post('/schemes/{scheme}/ajax', 'SchemeController@ajaxAction'); //All Ajax actions
Route::post('/schemes/{scheme}/ajax/{action}', 'SchemeController@ajaxAction'); //All Ajax actions

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

Route::any('/exchanges/{name}', 'ExchangeController@show'); //view all


Route::get('/exchanges/getprices', 'ExchangeController@getPrices');
Route::get('/exchanges/trade', 'ExchangeController@runTradingRules');
Route::get('/exchanges/resetCoins', 'ExchangeController@resetCoins');
Route::get('/exchanges/coinpusher', 'ExchangeController@coinPusher');
Route::get('/exchanges/getopenorders', 'ExchangeController@getOpenOrders');
Route::get('/exchanges/checkorders', function(){
	$exchange = new Exchanges();
	$exchange->checkForCompletedOrders();
});