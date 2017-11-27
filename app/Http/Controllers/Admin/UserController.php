<?php
/** TODO: Turn this into a base controller for all CMS modules
 * Sort out http vs ajax requests/responses
 * Split the actions & views into 2 functions
 * Work out how best to extend base controller where necessary for actual modules
 * Reduce use of unecessary functions for basically setting a view (permissions, create, edit etc)
 **/

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Events\UserRegistered;
use App\Activity;
use Illuminate\Support\Facades\View;

class UserController extends BaseController
{

	 public function __construct() {

	 	parent::__construct("users");


         //Check a specific permission, uses App/Middleware/CheckPermission
         $this->middleware(['permission:view users']);


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

        //Can only carry out actions if they have edit access
        $user = Auth::user();
        if(!$user->hasPermissionTo("edit users")) return false;

         if($request->isMethod('post')) {


            switch($request->input('action')) {

                case "store" :
                    return $this->store($request);
                break;

                case "update" :
                    return $this->update($request, $user);
                break;

                case "set_permissions" :
                    return $this->setPermissions($request, $user);
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


        //Can only view 'action' pages if they have edit access
        $user = Auth::user();

        parent::view($view);

        switch($view) {

            case "create" :
                if(!$user->hasPermissionTo("edit users")) abort(404);
                return $this->create();

            case "show" :
                return $this->show($user);

            case "edit" :
                if(!$user->hasPermissionTo("edit users")) abort(404);
                return $this->edit($user);

            case "permissions" :
                if(!$user->hasPermissionTo("edit users")) abort(404);
                return $this->permissions($user);

            case "" :
            case "index" :
            case "home" :
                return $this->index();

            default :
                abort(404);
                break;

        }

    }

      /**
     * Handle ajax calls
     *
     *
    public function ajax(Request $request, $view=false, User $user=null) {


        //If this wasn't an ajax call (something went wrong) go back to the last page
        if(!$request->ajax()) {
            return redirect()->back();
        }
        else {
            return json_encode($domain_details);
        }


    }
*/

    /**** ACTION FUNCTIONS ***/
  

    /* setPermissions
    **/
    public function setPermissions(Request $request, User $user) {

        if(is_array($request->input('roles') )) $roles = $request->input('roles'); else $roles = array();
       
        if(is_array($request->input('permissions') )) $permissions = $request->input('permissions'); else $permissions = array();

        $log_info = [
            'action' => "ROLES",
            'model' => "User",
            'model_id' => $user['id'],
            'attributes' => json_encode($roles),
            'original' => json_encode($user->getRoleNames())
        ];
        Activity::create($log_info);

        $log_info = [
            'action' => "PERMISSIONS",
            'model' => "User",
            'model_id' => $user['id'],
            'attributes' => json_encode($permissions),
            'original' => json_encode($user->permissions)
        ];
        Activity::create($log_info);

        $user->syncRoles($roles );
        $user->syncPermissions($permissions ); 

        return redirect('admin/'.$this->folder)->with('status-success', 'Permissions Set');

    }

   
    /**
     * Store a newly created resource in storage.
     * TODO: Send the user an email to set a password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         //Validate input and ensure email address is unique
        $request->validate([
            'email' => 'required|unique:users,email',
            'name' => ['required'],
        ]);

 
        //create the data array
        $data = array();
        $data = $request->all();
        $data['password'] = "";


        //Save to database
        $record = User::create($data);

        //Trigger the UserRegistered events
        event(new UserRegistered($record));

        $success = true;

        return $this->doActionResponse( $success, "User registered", "Registration failed");

    }


/**** VIEW FUNCTIONS **/

 /** index()
     * Homepage for this module
     * @return \Illuminate\Http\Response
     * Show the table or some sort of dashboard
    **/
    public function index() {

         $data['records'] = User::all();

         $data['record_table'] = $this->createUserTable();


         return $this->render('admin.'.$this->folder.'.home', $data);
    }


  /** permissions()
     * 
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     * 
     * Custom page for this module only
     * Set users roles & permissions
     **/
    public function permissions(User $user) {

        return $this->render('admin.'.$this->folder.'.permissions', array("record"=>$user));

    }


/**
     * Show the form for creating the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $this->addBreadcrumb("#", "Register User");
        return $this->render('admin.'.$this->folder.(request()->ajax() ? '.ajax' : '').'.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //return ajax view if that is what was requested
        return $this->render('admin.'.$this->folder.(request()->ajax() ? '.ajax' : '').'.show', array("record"=>$user));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
        return $this->render('admin.'.$this->folder.(request()->ajax() ? '.ajax' : '').'.edit', array("record"=>$user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //Validate input and ensure email address is unique
        $request->validate([
            'email' => 'required|unique:users,email,'.$user['id'],
            'name' => ['required'],
        ]);

        //create the data array
        $data = array();
        $data = $request->all();

        //Update model
        $user->fill($data);

        //Save to database
        $success = $user->update();

        return $this->doActionResponse( $success, "User updated", "Update failed");
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        $success = $user->delete();
        return $this->doActionResponse($success, "User deleted", "Delete failed");
    }


    //Some actions need additional content if responding with ajax
    protected function doActionResponse($ok, $success_text, $failed_text, $json=array()) {

        $ajax = array();

        if(request()->ajax()) {

             switch(request()->input('action')) {

                case "store" :
                case "update" :
                case "set_permissions" :
                case "delete" :
                    
                    $json['#record-table'] = $this->createUserTable();

                break;
            }

        }

        return parent::doActionResponse($ok, $success_text, $failed_text, $json);
    }

    //Create the html to display the user table with ajax
    //TODO: datatables or angular tables
    function createUserTable() {
        $data['records'] = User::all();
        return view('admin.'.$this->folder.'.ajax.table', $data)->render();
    }
    
}
