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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Url;

class BaseController extends Controller
{

    //template folder and url path after '/admin'
    protected $folder;
    protected $module;
    protected $model_name;
    protected $page_subtitle;

    //Breadcrumb array
    protected $breadcrumb_array;


	public function __construct($module) {

	 	//Only logged in admin users can access these pages - admin login & password reset is same as normal users
         $this->middleware(['role:administrator']);

         $this->module = $module;
         $this->folder = Config::get('modules.'.$this->module.'.folder');
         $this->model_name = Config::get('modules.'.$this->module.'.model_name');

         //First breadcrumb is always the admin dashboard
         $this->addBreadcrumb(route('admin'), "Dashboard");


         //Add module breadcrumb
         if($this->folder != "home") $this->addBreadcrumb("/admin/".$this->folder, Config::get('modules.'.$this->module.'.title'));
    }


    /** view()
     * Basic view function, called at start of view process
     **/
    protected function view($view) {

        //Page subtitle
        switch($view) {
            case "create" :
                $this->page_subtitle = "Create ".$this->model_name;
                break;
            case "edit" :
                $this->page_subtitle = "Amend ".$this->model_name;
                break;

            case "show" :
                $this->page_subtitle ="View ".$this->model_name;
                break;
            case "list" :
                $this->page_subtitle = "List ".$this->model_name;
                break;
            case "permissions" :
                $this->page_subtitle = $this->model_name. " Permissions";
                break;
        }
        
    }

    /**render()
     * Handles any final functionality before displaying the view
    **/
    protected function render($template, $data=array()) {

        //Add extra data to the array
        $data['breadcrumb_array'] = $this->breadcrumb_array;
        $data['activity_link'] = false; //Need to create links to activity module

        //Metas & titles

        $data['meta_title'] = Config::get('modules.'.$this->module.'.title');

        $data['page_title'] = Config::get('modules.'.$this->module.'.title');

        $data['page_subtitle'] = $this->page_subtitle;
        if($data['page_subtitle']) $data['meta_title'] .= " - ".$data['page_subtitle'];


        $data['page_shortcut'] = Auth::User()->hasAdminShortcut("/".request()->path());

        return view($template, $data);
    }


    /*doActionResponse()
     * Returns the success or error message as a json string for ajax requests or a flash message for http
     * Can be overridden to include additional content in the ajax response
     **/
    protected function doActionResponse($ok, $success_text, $failed_text, $json = array()) {

         //Pick which text we want
        if($ok) {
            $text = $success_text;
            $status = "success";
        }
        else {
            $text = $failed_text;
            $status = "danger";
        }

        if(request()->ajax()) {
            //Return JSON string
            $json[$status] = $text;
            echo json_encode($json);
            die();
        }
        else {
            //Redirect & flash status
            return redirect('admin/'.$this->folder)->with('status-'.$status, $text);
        }

    }

    protected function addBreadcrumb($url, $text) {
        $crumb = array();
        $crumb['url'] = $url;
        $crumb['title'] = $text;
        $this->breadcrumb_array[] = $crumb;
    }

    protected function getBreadcrumbs() {
        return $this->breadcrumb_array;
    }
    
}
