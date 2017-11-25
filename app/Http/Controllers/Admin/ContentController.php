<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Page;

class ContentController extends BaseController
{

	 public function __construct() {

        parent::__construct("content");
	 	

    }

      /** run()
     * All non ajax calls to this controller pass though this function
     * @param  \Illuminate\Http\Request  $request
     * @param  $view
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function run(Request $request, $view=false, User $user=null) {

        //First try to get a response from posted actions
        $response = $this->actions($request, $user);

        //if there is none then get a response from the view
        if(!$response)
            $response =  $this->view($view, $user);

        return $response;
    }


    /* actions()
     * All posted actions get processed here
    **/
    public function actions(Request $request, User $user=null) {

         if($request->isMethod('post')) {

            switch($request->input('action')) {

                case "store" :
                    return $this->store($request);
                break;

                case "update" :
                    return $this->update($request, $user);
                break;

                case "delete" :
                    return $this->destroy($user);
                break;
            }

        }
    }


    /* Respond with a view
    **/
    public function view($view=false, User $user=null) {

        parent::view($view);

        switch($view) {

            case "create" :
                return $this->create();

            case "show" :
                return $this->show($user);

            case "edit" :
                return $this->edit($user);

            case "" :
            case "index" :
            case "home" :
                return $this->index();

            default :
                abort(404);
                break;

        }

    }


    //Standard admin pages
    public function index() {

         $data = array();

         $data['pages'] = Page::all();

         return $this->render('admin.'.$this->folder.'.home', $data);
    }


}
