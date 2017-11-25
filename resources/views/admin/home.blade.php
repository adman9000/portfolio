@extends('layouts.admin')

@section('content')
    <div class="row">
       <div class='col-md-12'>
                <div class='widget white-bg style1'>
                   

                    <h3>CMS Progress</h3>

                    <p>TODO:<br />
                    Add admin_id and published fields to activity & allow rollback & saving without publishing<br />
                    Publish, edit, view permissions for modules<br />
                    Publish & edit permissions for content<br />
                    <br />
                    Use env or config to set which models are recorded in activity log - improve recording of permission changes, move back to trait/extend the permission trait.
                    <br />
                    Add alerts (toastr)<br />
                    Search autocomplete (bloodhound)
                </p>

            </div>
        </div>
    </div>

    <div class='row'>
            <div class='col-md-4'>
                <div class='widget white-bg style1'>
                    <h3>Your Recent Activity <span class='small pull-right'><a href='#'>view all</a></span></h3>
                    <ul class="list-group clear-list">
                        
                        <? /*foreach($admin_activity as $activity) { ?>
                        <li class="list-group-item first-item">
                            <span class="pull-right"> <?=$activity['_full_date_added']?> </span>
                            <?=$activity['details']?>
                        </li>
                        <? }*/ ?>
                        
                    </ul>
                </div>
            </div>
            
            <div class='col-md-4'>
                <div class='widget white-bg style1'>
                    <h3>Your Shortcuts</h3>
                    
                    <div id='admin-shortcuts'>
                        <ul class="list-group clear-list">

                            @foreach(Auth::user()->adminShortcuts as $myshortcut) 

                                <li class="list-group-item first-item">
                                    <a href='{{ $myshortcut['url'] }}'>{{ $myshortcut['title'] }}</a>
                                    <a class='pull-right' data-async target="#admin-shortcut-{{ $myshortcut['id'] }}" href='/admin/home' data-post="action=remove_shortcut&shortcut_id={{ $myshortcut['id'] }}" data-onsuccess="removeParent"><i class='fa fa-remove text-danger'></i></a>
                                    
                                </li>
                            
                            @endforeach

                        </ul>
                    </div>

                </div>
            </div>
            <div class="col-md-4">
                <div class="widget white-bg style1">
                    <h3>Your Alerts</h3>
                    <ul class="list-group clear-list"> <?
                        /*foreach($admin_alerts as $alert) { ?>
                            <li class="list-group-item first-item">
                                <a href="<?=$alert['url']?>"><?=$alert['title']?></a>
                            </li> <?
                        }*/ ?>
                    </ul>
                </div>
            </div>
        </div>

@endsection
