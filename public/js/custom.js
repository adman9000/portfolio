
$(document).ready(function() {

	$(".datatable").DataTable();
})

//Ajax loaded modal popup using content from the href and wrapping in modal code 
$("body").on("click", '[data-toggle="modal-ajax"]', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    if (url.indexOf('#') == 0) {
        $(url).modal('open');
    } else {
        $.get(url, function (data) {
            $("#ajax-modal").remove();
            if (data) $(data).modal();
        });
    }
});


	//Convert a form to ajax submission by adding 'data-async' attribute	
	$("body").on('submit', "form[data-async]", function(event) {
		event.preventDefault();
		event.stopPropagation();
        var form = $(this);
		ajaxSubmitForm(form);   
    });

    var $form;
	function ajaxSubmitForm(form) {
		var data = new FormData(form[0]);
		$form = $(form);
			 $target = $($form.attr('data-target'));
			$.ajax({
				type: $form.attr('method'),
				url: $form.attr('action'),
				data: data,
				contentType: false,
				processData: false,
				success: function(data, status) {
				
					try {
						var json = $.parseJSON(data);
						displayJson(json);
					}
					catch(e) {
						$target.html(data);
					}
				}
			});		
	}

	//Ajax calls generally return json data. This is how we display it
function displayJson(json) {
	
	$.each(json, function (item, value) {
		if(item =="modal")
			showModal(value);
		if(item =="error")
			showError(value);
		if(item == "alert")
			showAlert(value);
		if(item == "warning")
			showWarning(value);
		if(item == "success")
			showSuccess(value);
		if(item == "content") $target.html(value);
		else {
			$(item).html(value);
			$(item).show();
		}
	});

}

function showModal(content) {
	if(content)  $('<div class="modal  fade" id="ajax-modal"><div class="modal-dialog"><div class="modal-content">' + content + '  </div></div></div>').modal();
}	
function showAlert(txt) {
	$("#msg").html('<div class="alert  alert-dismissable">		  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>		  '+txt+'		</div>');
	//noty({text: txt,layout:'topRight',type:'alert',timeout:2000});
}
function showError(txt) {
	$("#msg").html('<div class="alert alert-danger alert-dismissable">		  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>		  '+txt+'		</div>');
	//noty({text: txt,layout:'top',type:'error',timeout:2000});
}
function showWarning(txt) {
	$("#msg").html('<div class="alert alert-warning alert-dismissable">		  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>		  '+txt+'		</div>');
	//noty({text: txt,layout:'top',type:'warning',timeout:2000});
}
function showSuccess(txt) {
	$("#msg").html('<div class="alert alert-success alert-dismissable">		  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>		  '+txt+'		</div>');
	//noty({text: txt,layout:'topLeft',type:'success',timeout:2000});
}
function showInformation(txt) {
	$("#msg").html('<div class="alert alert-info alert-dismissable">		  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>		  '+txt+'		</div>');
	//noty({text: txt,layout:'topRight',type:'information',timeout:2000});
}


	$(document).ready(function() {

		$("body").on("change", "#modal-set-price", setModalPrices);
	})

	function setModalPrices() {

		var set_price = parseFloat($("#modal-set-price").val());
		console.log(set_price);

		var buy_percent = parseFloat($("#modal-buy-percent").val());
		var buy_price = set_price - (set_price * buy_percent/100);
		$("#modal-buy-price").val(buy_price);

		var trigger_1_percent = parseFloat($("#modal-trigger-1-percent").val());
		var trigger_1_price = set_price + (set_price * trigger_1_percent/100);
		$("#modal-trigger-1-price").val(trigger_1_price);

		var trigger_2_percent = parseFloat($("#modal-trigger-2-percent").val());
		var trigger_2_price = set_price + (set_price * trigger_2_percent/100);
		$("#modal-trigger-2-price").val(trigger_2_price);

	}