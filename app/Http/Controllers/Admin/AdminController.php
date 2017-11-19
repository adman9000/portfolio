<?php
/* AdminController
 * Used for admin dashboard and not much else
 **/

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminController extends BaseController
{

	 public function __construct() {

        parent::__construct("dashboard");
	 	

    }

    /** run()
     * All non ajax calls to this controller pass though this function
     * @param  \Illuminate\Http\Request  $request
     * @param  $view
     * @return \Illuminate\Http\Response
     */
    public function run(Request $request, $view=false) {

        //First try to get a response from posted actions
        $response = $this->actions($request);

        //if there is none then get a response from the view
        if(!$response)
            $response =  $this->view($view);

        return $response;
    }


    /* actions()
     * All posted actions get processed here
    **/
    public function actions(Request $request) {

        // if($request->isMethod('post')) {

            switch($request->input('action')) {

				//Adding & removing shortcuts
				case "add_shortcut" :

					$shortcut = new \App\AdminShortcut();
					$shortcut->url = $request->input('url');
					$shortcut->title = $request->input('title');
					$shortcut->user_id = Auth::user()->id;
					$success = $shortcut->save();
       		 		return $this->doActionResponse( $success, "Shortcut created", "Shortcut failed");

				break;

                case "remove_shortcut" :
                    
                    $shortcut = \App\AdminShortcut::find($request->input('shortcut_id'));

					$success = $shortcut->delete();

       		 		return $this->doActionResponse( $success, "Shortcut deleted", "Delete failed");

                break;

            }

        //}
    }


    /* Respond with a view
    **/
    public function view($view=false) {

        parent::view($view);

        switch($view) {

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


         $user = Auth::user();

         return $this->render('admin.home');
    }


}
