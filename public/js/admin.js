/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 62);
/******/ })
/************************************************************************/
/******/ ({

/***/ 62:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(63);


/***/ }),

/***/ 63:
/***/ (function(module, exports) {



$(document).ready(function () {

    $("body").on("click", ".submitform", function () {
        var fid = $(this).data("form");
        console.log(fid);
        $(fid).submit();
    });

    $('[data-toggle="tooltip"]').tooltip();
});

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
$("body").on('submit', "form[data-async]", function (event) {
    event.preventDefault();
    event.stopPropagation();
    var form = $(this);
    ajaxSubmitForm(form);
});

//Convert a link to load ajax content by adding 'data-async' attribute
//Uses data attributes
//data-post for postdata
//data-onsuccess to call a function on a successfull ajax return (with a success value in the json string)
$("body").on('click', 'a[data-async]', function (event) {
    event.preventDefault();
    event.stopPropagation();

    var $form = $(this);
    $target = $($form.attr('target'));
    var postdata = $(this).attr("data-post");
    $.ajax({
        type: "GET",
        data: postdata,
        url: $form.attr('href'),
        success: function success(data, status) {
            try {
                var json = $.parseJSON(data);
                displayJson(json);

                if (json['success']) {
                    if ($form.attr("data-onsuccess")) {
                        var func = $form.attr("data-onsuccess");
                        console.log(func + "($form)");
                        eval(func + "($form)");
                    }
                }
            } catch (e) {
                $target.html(data);
            }

            // Hook.call( 'ajaxClickSuccess', [ data ] );
        }
    });
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
        success: function success(data, status) {

            try {
                var json = $.parseJSON(data);
                displayJson(json);
            } catch (e) {
                $target.html(data);
            }
        }
    });
}

function removeParent($elem) {
    $elem.parent().remove();
}

function setWarning($elem) {
    $elem.children("span").addClass("text-warning");
}

//Ajax calls generally return json data. This is how we display it
function displayJson(json) {
    console.log(json);
    $.each(json, function (item, value) {
        console.log(item);
        console.log(value);
        if (item == "modal") showModal(value);else if (item == "error" || item == "alert" || item == "warning" || item == "success") {
            $target.html('<div class="alert  alert-dismissable alert-' + item + '"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>       ' + value + '     </div>');
        } else if (item == "content") $target.html(value);else {
            $(item).html(value);
            $(item).show();
        }
    });
}

function showModal(content) {
    if (content) $('<div class="modal  fade" id="ajax-modal"><div class="modal-dialog"><div class="modal-content">' + content + '  </div></div></div>').modal();
}
function showAlert(txt) {
    $("#msg").html('<div class="alert  alert-dismissable">        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>        ' + txt + '       </div>');
    //noty({text: txt,layout:'topRight',type:'alert',timeout:2000});
}
function showError(txt) {
    $("#msg").html('<div class="alert alert-danger alert-dismissable">        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>        ' + txt + '       </div>');
    //noty({text: txt,layout:'top',type:'error',timeout:2000});
}
function showWarning(txt) {
    $("#msg").html('<div class="alert alert-warning alert-dismissable">       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>        ' + txt + '       </div>');
    //noty({text: txt,layout:'top',type:'warning',timeout:2000});
}
function showSuccess(txt) {
    $("#msg").html('<div class="alert alert-success alert-dismissable">       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>        ' + txt + '       </div>');
    //noty({text: txt,layout:'topLeft',type:'success',timeout:2000});
}
function showInformation(txt) {
    $("#msg").html('<div class="alert alert-info alert-dismissable">          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>        ' + txt + '       </div>');
    //noty({text: txt,layout:'topRight',type:'information',timeout:2000});
}

/* done using standard function now
//Save page shortcut
    $("body").on("click", ".shortcut-page", function() {
        var data = "action=add_shortcut&url="+$(this).data("shortcut-url") + "&title=" + $(this).data("shortcut-title");
        var path = window.location.pathname;
        $.ajax({method : "post", "url" : path, data : data, success: function(data) { displayJson($.parseJSON(data)); }});
        $(this).children("span").addClass("text-warning");
    });
    **/

/***/ })

/******/ });