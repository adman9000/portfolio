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
use adman9000\coinmarketcap\CoinmarketcapAPIFacade;


Auth::routes();

Route::get("/test", function() { 

	//Role::create(['name' => 'administrator']);
	//Role::create(['name' => 'member']);
	//Permission::create(['name' => 'edit users']);
	//Permission::create(['name' => 'view users']);
	//Permission::create(['name' => 'publish content']);

         $user = Auth::user();

         //$user->assignRole('administrator');
         $user->givePermissionTo('edit users');

	echo "OK";
	dd($user->hasRole("administrator"));
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



//Dashboard ROUTES
Route::get("/dashboard", "Dashboard\DashboardController@index")->name("dashboard");

Route::prefix('dashboard')->group(function() {

	//Coins
	Route::get('/coins', 'Dashboard\CoinController@index')->name('coins'); //view all
	Route::get('/coins/create', 'Dashboard\CoinController@create'); // create form
	Route::get('/coins/charts', 'Dashboard\CoinController@charts')->name('charts'); // charts
	Route::get('/coins/charts/{time}', 'Dashboard\CoinController@charts')->name("charts24"); // charts
	Route::get('/coins/{coin}/edit', 'Dashboard\CoinController@edit'); //edit form
	Route::get('/coins/{coin}', 'Dashboard\CoinController@show'); //view
	Route::post('/coins/tobtc/{coin}', 'Dashboard\CoinController@tobtc'); // convert to btc
	Route::get('/coins/tobtc/{coin}', 'Dashboard\CoinController@tobtc'); // convert to btc
	Route::post('/coins', 'Dashboard\CoinController@store'); //Submit new
	Route::patch('/coins/{coin}', 'Dashboard\CoinController@update'); //Submit edit
	Route::delete('/coins/{coin}', 'Dashboard\CoinController@destroy'); //Submit delete

	//Schemes
	Route::get('/schemes', 'Dashboard\SchemeController@index')->name('schemes'); //view all
	Route::get('/schemes/create', 'Dashboard\SchemeController@create'); // create form
	Route::get('/schemes/{scheme}/edit', 'Dashboard\SchemeController@edit'); //edit form
	Route::get('/schemes/{scheme}/coins', 'Dashboard\SchemeController@coins'); //edit form
	Route::get('/schemes/{scheme}/orders', 'Dashboard\SchemeController@orders'); //view orders/transaction records for this scheme
	Route::get('/schemes/{scheme}', 'Dashboard\SchemeController@show'); //view
	Route::post('/schemes', 'Dashboard\SchemeController@store'); //Submit new
	Route::patch('/schemes/{scheme}', 'Dashboard\SchemeController@update'); //Submit edit
	Route::patch('/schemes/{scheme}/coins', 'Dashboard\SchemeController@setCoins'); //Set Coins
	Route::delete('/schemes/{scheme}', 'Dashboard\SchemeController@destroy'); //Submit delete
	Route::patch('/schemes/{scheme}/enable', 'Dashboard\SchemeController@enable'); //Submit enable.disable form

	Route::get('/schemes/{scheme}/ajax/{view}/{id}', 'Dashboard\SchemeController@ajaxView'); //All Ajax views
	Route::post('/schemes/{scheme}/ajax', 'Dashboard\SchemeController@ajaxAction'); //All Ajax actions
	Route::post('/schemes/{scheme}/ajax/{action}', 'Dashboard\SchemeController@ajaxAction'); //All Ajax actions

	//Transactions
	Route::get('/transactions', 'Dashboard\TransactionController@index')->name('transactions'); //view all
	Route::get('/transactions/create', 'Dashboard\TransactionController@create'); // create form
	Route::get('/transactions/{transaction}/edit', 'Dashboard\TransactionController@edit'); //edit form
	Route::get('/transactions/{transaction}', 'Dashboard\TransactionController@show'); //view
	Route::post('/transactions', 'Dashboard\TransactionController@store'); //Submit new
	Route::patch('/transactions/{transaction}', 'Dashboard\TransactionController@update'); //Submit edit
	Route::delete('/transactions/{transaction}', 'Dashboard\TransactionController@destroy'); //Submit delete

	//Exchanges

	Route::get('/exchanges', 'Dashboard\ExchangeController@index')->name('exchanges'); //view all
	Route::post('/exchanges', 'Dashboard\ExchangeController@index'); //view all

	Route::any('/exchanges/{name}', 'Dashboard\ExchangeController@show'); //view all


	Route::get('/exchanges/getprices', 'Dashboard\ExchangeController@getPrices');
	Route::get('/exchanges/trade', 'Dashboard\ExchangeController@runTradingRules');
	Route::get('/exchanges/resetCoins', 'Dashboard\ExchangeController@resetCoins');
	Route::get('/exchanges/coinpusher', 'Dashboard\ExchangeController@coinPusher');
	Route::get('/exchanges/getopenorders', 'Dashboard\ExchangeController@getOpenOrders');
	Route::get('/exchanges/checkorders', function(){
		$exchange = new Exchanges();
		$exchange->checkForCompletedOrders();
	});

	//dashboard fallback
	Route::any("/{view?}/{id?}", "Dashboard\DashboardController@run");

});


//CMC
Route::get('/cmc', function(){
		$exchanges = new Exchanges();
		//$exchanges->setupCoins();
		//$exchanges->saveCMCPrices();
		//$exchanges->saveExchangePrices();
		$exchanges->calculatePortfolios();
	});

//FRONT END ROUTES
Route::get('/', 'WebsiteController@index')->name('home');
Route::fallback('WebsiteController@index');
