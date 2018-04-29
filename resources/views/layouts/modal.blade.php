<div class="modal " id="ajax-modal"><div class="modal-dialog"><div class="modal-content">

<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@yield('title')
</h4>
      </div>

<div class="modal-body">
<div id='modal-msg'></div>

@yield('content')


    </div>

@yield('footer')

</div></div></div>