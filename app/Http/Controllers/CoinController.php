<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coin;
use App\CoinPrice;

class CoinController extends Controller
{
    //

     public function index()
    {

        $data = array();
        $data['coins'] = Coin::all();
        return view('coins.index', $data);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view("coins.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         //Validate input
    	$this->validate(request(), [
    		'code' => 'required|min:2|max:4',
    		'name' => 'required']
    		);

        //only safe if fillable/guarded set
 		Coin::create(request()->all());

 		return $this->index();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Coin $coin)
    {
        //
        return view("coins.show", array("coin" => $coin));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Coin $coin)
    {
        //
        return view("coins.edit", array("coin" => $coin));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coin $coin)
    {
        //
         //Validate input
    	$this->validate(request(), [
    		'code' => 'required|min:2|max:4',
    		'name' => 'required']
    		);

        //TODO: must be better way for editing lots of fields..
    	$coin->code = $request->code;
		$coin->name = $request->name;
		$coin->save();

 		return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coin $coin)
    {
        //
        $coin->delete();
 		return $this->index();
    }
}
