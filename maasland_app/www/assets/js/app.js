$().ready(function() {
    $sidebar = $('.sidebar');
    $sidebar_img_container = $sidebar.find('.sidebar-background');

    $full_page = $('.full-page');

    $sidebar_responsive = $('body > .navbar-collapse');

    window_width = $(window).width();


//////
    $.validator.setDefaults({
        //debug: true, // blocks submit
        // errorElement: 'span', //default input error message container
        // errorClass: 'help-inline', // default input error message class
        // focusInvalid: false, // do not focus the last invalid input)
        // highlight: function (element) { // hightlight error inputs
        //     $(element).closest('.control-group').addClass('error'); // set error class to the control group
        // },
        // unhighlight: function (element) { // revert the change dony by hightlight
        //     $(element).closest('.control-group').removeClass('error'); // set error class to the control group
        // },
        // added submitHandler only for demo
        submitHandler: function(form) { 
            // console.log(this);
            // console.log(form);
            // console.log(event);
            form.submit();
            //return false; 
        }
    });

    if ($("#controllerForm").length != 0) {
        $("#controller_chooser").click(function () {
            //Get text or inner html of the selected option
            var item = $("#controller_chooser option:selected").val();
            //console.log(item);
            if(item){
                var jsonData = JSON.parse(item);
                //console.log(jsonData);
                $("#controller_name").val(jsonData[6]);
                $("#controller_ip").val(jsonData[7]);
                //$("#controller_remarks").val(jsonData);
            }
        });

        $("#scan_key").click(function () {
            $button_text = '<i class="fa fa fa-search"></i>'+resource.controllerSearchButton;
            //$('.loaderImage').show();
            app.addSpinnerToButton(this, true, $button_text);
            var self = this;

            $.ajax({
                //url: endpoint + "?key=" + apiKey + " &q=" + $( this ).text(),
                //contentType: "application/json",
                //dataType: 'json',
                url: "/?/available_controllers.json",
                success: function(result){
                    var jsonData = JSON.parse(result);
                    //console.log(jsonData.length);
                    if(jsonData.length > 0) {
                        $("#controller_chooser").html($.map(jsonData, function(o) {
                            //console.log(o);
                            return $('<option>', {
                                class: "list_item", 
                                value: JSON.stringify(o),
                                text: o[6]+ " (" +o[7] + ")"
                            });
                        }));
                    } else {
                        swal(resource.sorry, resource.noControllersFound);
                    //     $("#controller_chooser").html('<option>...</option>');
                    }
                    //$("#controller_ip").val(result);
                    //$('.loaderImage').hide();
                    app.addSpinnerToButton(self, false, $button_text);
                },
                error: function (response) {
                   //Handle error
                   //$('.loaderImage').hide();
                   app.addSpinnerToButton(self, false, $button_text);
                }
            });
        });
    };

    if ($("#userForm").length != 0) {
        $("#scan_key").click(function () {
            $button_text = resource.useLastKeyButton;
            //$('.loaderImage').show();
            app.addSpinnerToButton(this, true, $button_text);
            var self = this;

            $.ajax({
                //url: endpoint + "?key=" + apiKey + " &q=" + $( this ).text(),
                //contentType: "application/json",
                //dataType: 'json',
                url: "/?/last_scanned_key.json",
                success: function(result){
                    $("#user_keycode").val(result);
                    //$('.loaderImage').hide();
                    app.addSpinnerToButton(self, false, $button_text);
                },
                error: function (response) {
                   //Handle error
                   //$('.loaderImage').hide();
                   app.addSpinnerToButton(self, false, $button_text);
                }
            });
        });
    };


    if ($("#timezoneForm").length != 0) {
        //https://github.com/nikolasmagno/jquery-weekdays
        //console.log("init timezoneForm");
        timezoneFormInit();
    };

    if ($(".settingsForm").length != 0) {
        //console.log("init settingsForm");
        //$("#settingsForm").validate();
        settingsFormValidation();
    };

    if ($(".networkForm").length != 0) {
        //console.log("init networkForm");
        //$("#settingsForm").validate();
        networkFormValidation();
    };

    if ($("#initMyForm").length != 0) {
        console.log("init myForm");
        initMyForm();
    };

    // Init Datetimepicker
    if ($("#datetimepicker").length != 0) {
        $('.datetimepicker').datetimepicker({
            format: 'DD/MM/YYYY HH:mm',
            widgetPositioning: {
                horizontal: 'auto',
                vertical: 'auto'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });

        $('.datepicker').datetimepicker({
            format: 'DD/MM/YYYY',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });

        $('.timepicker').datetimepicker({
            format: 'HH:mm',
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });

    };
    fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();

    if (window_width > 767 && fixed_plugin_open == 'Dashboard') {
        if ($('.fixed-plugin .dropdown').hasClass('show-dropdown')) {
            $('.fixed-plugin .dropdown').addClass('show');
        }

    }

    $('.fixed-plugin a').click(function(event) {
        // Alex if we click on switch, stop propagation of the event, so the dropdown will not be hide, otherwise we set the  section active
        if ($(this).hasClass('switch-trigger')) {
            if (event.stopPropagation) {
                event.stopPropagation();
            } else if (window.event) {
                window.event.cancelBubble = true;
            }
        }
    });

    $('.fixed-plugin .background-color span').click(function() {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        var new_color = $(this).data('color');

        if ($sidebar.length != 0) {
            $sidebar.attr('data-color', new_color);
        }

        if ($full_page.length != 0) {
            $full_page.attr('filter-color', new_color);
        }

        if ($sidebar_responsive.length != 0) {
            $sidebar_responsive.attr('data-color', new_color);
        }
    });

    $('.fixed-plugin .img-holder').click(function() {
        $full_page_background = $('.full-page-background');

        $(this).parent('li').siblings().removeClass('active');
        $(this).parent('li').addClass('active');


        var new_image = $(this).find("img").attr('src');

        if ($sidebar_img_container.length != 0 && $('.switch-sidebar-image input:checked').length != 0) {
            $sidebar_img_container.fadeOut('fast', function() {
                $sidebar_img_container.css('background-image', 'url("' + new_image + '")');
                $sidebar_img_container.fadeIn('fast');
            });
        }

        if ($full_page_background.length != 0 && $('.switch-sidebar-image input:checked').length != 0) {
            var new_image_full_page = $('.fixed-plugin li.active .img-holder').find('img').data('src');

            $full_page_background.fadeOut('fast', function() {
                $full_page_background.css('background-image', 'url("' + new_image_full_page + '")');
                $full_page_background.fadeIn('fast');
            });
        }

        if ($('.switch-sidebar-image input:checked').length == 0) {
            var new_image = $('.fixed-plugin li.active .img-holder').find("img").attr('src');
            var new_image_full_page = $('.fixed-plugin li.active .img-holder').find('img').data('src');

            $sidebar_img_container.css('background-image', 'url("' + new_image + '")');
            $full_page_background.css('background-image', 'url("' + new_image_full_page + '")');
        }

        if ($sidebar_responsive.length != 0) {
            $sidebar_responsive.css('background-image', 'url("' + new_image + '")');
        }
    });

    $('.switch-image input').on("switchChange.bootstrapSwitch", function() {

        $full_page_background = $('.full-page-background');

        $input = $(this);

        if ($input.is(':checked')) {
            if ($sidebar_img_container.length != 0) {
                $sidebar_img_container.fadeIn('fast');
                $sidebar.attr('data-image', '#');
            }

            if ($full_page_background.length != 0) {
                $full_page_background.fadeIn('fast');
                $full_page.attr('data-image', '#');
            }

            background_image = true;
        } else {
            if ($sidebar_img_container.length != 0) {
                $sidebar.removeAttr('data-image');
                $sidebar_img_container.fadeOut('fast');
            }

            if ($full_page_background.length != 0) {
                $full_page.removeAttr('data-image', '#');
                $full_page_background.fadeOut('fast');
            }

            background_image = false;
        }
    });

    $('.switch-mini input').on("switchChange.bootstrapSwitch", function() {
        $body = $('body');

        $input = $(this);

        if (lbd.misc.sidebar_mini_active == true) {
            $('body').removeClass('sidebar-mini');
            lbd.misc.sidebar_mini_active = false;

            if (isWindows) {
                $('.sidebar .sidebar-wrapper').perfectScrollbar();
            }

        } else {

            $('.sidebar .collapse').collapse('hide').on('hidden.bs.collapse', function() {
                $(this).css('height', 'auto');
            });

            if (isWindows) {
                $('.sidebar .sidebar-wrapper').perfectScrollbar('destroy');
            }

            setTimeout(function() {
                $('body').addClass('sidebar-mini');

                $('.sidebar .collapse').css('height', 'auto');
                lbd.misc.sidebar_mini_active = true;
            }, 300);
        }

        // we simulate the window Resize so the charts will get updated in realtime.
        var simulateWindowResize = setInterval(function() {
            window.dispatchEvent(new Event('resize'));
        }, 180);

        // we stop the simulation of Window Resize after the animations are completed
        setTimeout(function() {
            clearInterval(simulateWindowResize);
        }, 1000);

    });

    $('.switch-nav input').on("switchChange.bootstrapSwitch", function() {
        $nav = $('nav.navbar').first();

        $nav.toggleClass("navbar-fixed");

        // if($nav.hasClass('navbar-fixed')){
        //     $nav.removeClass('navbar-fixed').prependTo('.main-panel');
        // } else {
        //     $nav.prependTo('.wrapper').addClass('navbar-fixed');
        // }

    });
    $('.lockIcon').each(function(index, value) {
        console.log(value);
        //$statusIcon = $(this);
        var url = $( value ).data("url");
        var door = $( value ).data("key");
        console.log("lockIcon:" +door+ " url=" +url);
        $.ajax({
            url: $( value ).data("url")
            ,timeout:6000 //3 second timeout
            // ,async:true
            // ,crossDomain:true
        }).done(function(response){
            result = safelyParseJSON(response);
            if (result) {
                console.log(result);
                $tooltip = "Version "+result['version']+"-"+result['1']+"-"+result['2'];
                console.log("Door:" +door+ "=" +result[door]);

                if(result[door]==="1") {
                    $( value ).html('<i alt="'+$tooltip+'" title="'+$tooltip+
                    '" class="fa fa-lg fa-unlock-alt text-success"></i>'); 
                } else {
                    $( value ).html('<i alt="'+$tooltip+'" title="'+$tooltip+
                    '" class="fa fa-lg fa-lock text-warning"></i>'); 
                }
            } 
        }).fail(function(jqXHR, textStatus){
            //console.log(textStatus);
            if(textStatus === 'timeout')
            {     
                $( value ).html('<i class="fa fa-lg fa-times text-danger"></i>'); 
            }
        });
    });    
    $('.statusIcon').each(function(index, value) {
        //console.log(value);
        //$statusIcon = $(this);
        $url = $( value ).data("url");
        //console.log("statusIcon:" +$url);
        $.ajax({
            url: $( value ).data("url")
            ,timeout:6000 //3 second timeout
            // ,async:true
            // ,crossDomain:true
        }).done(function(response){
            result = JSON.parse(response);
            //console.log(result);
            $tooltip = "Version "+result['version']+"-"+result['1']+"-"+result['2'];
            $( value ).html('<i alt="'+$tooltip+'" title="'+$tooltip+
                '" class="fa fa-check text-success"></i>'); 
        }).fail(function(jqXHR, textStatus){
            //console.log(textStatus);
            if(textStatus === 'timeout')
            {     
                $( value ).html('<i class="fa fa-times text-danger"></i>'); 
            }
        });
    });


    //used at reports and users
    $('#datatables').DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [25, 50, -1],
            [25, 50, "All"]
        ],
        order: [[0, "desc"]],
        responsive: true,
        language: {
            //decimal:        "",
            emptyTable:     resource.tableEmptyTable,
            info:           resource.tableInfo,
            infoEmpty:      resource.tableInfoEmpty,
            infoFiltered:   resource.tableInfoFiltered,
            infoPostFix:    "",
            thousands:      ",",
            lengthMenu:     resource.tableLengthMenu,
            loadingRecords: "Loading...",
            processing:     "",
            search:         "_INPUT_",
            searchPlaceholder: resource.tableSearchPlaceholder,
            zeroRecords:    resource.tableZeroRecords,
            paginate: {
                first:      resource.tableButtonFirst,
                last:       resource.tableButtonLast,
                next:       resource.tableButtonNext,
                previous:   resource.tableButtonPrev
            }
        }
    });
});

app = {
    //Button spinner
    addSpinnerToButton: function(button, showSpinner, $text) {
        //console.log("buttonSpinner="+showSpinner);
        button.disabled=showSpinner; 
        button.innerHTML=showSpinner ? '<i class="fa fa-spinner fa-spin"></i> '+resource.loading:$text;
        //console.log(button);
    }, 
    //Button spinner
    addSpinnerWithLinkToButton: function(button, showSpinner, buttonText, buttonLink) {
        //console.log("buttonSpinner="+showSpinner);
        button.disabled=showSpinner; 
        button.innerHTML=showSpinner ? '<i class="fa fa-spinner fa-spin"></i>'+resource.loading : buttonText;
        //console.log(button);
        window.location.href = buttonLink;
    }, 
    // Background, Ajax call
    ajaxCall: function(url) {
        $.ajax({
            url: url,
            success: function(result){
                //console.log(result);
            }
        });
    }, 
    // Sweet Alerts
    timerAlert: function(message, time, url) {
        $.ajax({
            //url: endpoint + "?key=" + apiKey + " &q=" + $( this ).text(),
            //contentType: "application/json",
            //dataType: 'json',
            url: url,
            success: function(result){
                //console.log(result);
            }
        })
        swal({
            title: resource.timerAlertTitle,
            text: message,
            timer: time,
            showConfirmButton: true
        });
    },
    areYouSure: function(that, title, text) {
        swal({
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn btn-info btn-fill",
            confirmButtonText: resource.confirmDeletion,
            cancelButtonClass: "btn btn-danger btn-fill",
            cancelButtonText: resource.cancel,
            closeOnConfirm: false,
        }, function() {
            var f = document.createElement('form'); 
            f.style.display = 'none'; 
            that.parentNode.appendChild(f); 
            f.method = 'POST'; 
            f.action = that.href; 
            var m = document.createElement('input'); 
            m.setAttribute('type', 'hidden'); 
            m.setAttribute('name', '_method'); 
            m.setAttribute('value', 'DELETE'); 
            f.appendChild(m); 
            f.submit();
            swal(resource.confirmDeletion2, resource.confirmDeletionText2, "success");
        });
    },


    showSwal: function(type) {
        if (type == 'basic') {
            swal("Here's a message!");

        } else if (type == 'title-and-text') {
            swal("Here's a message!", "It's pretty, isn't it?")

        } else if (type == 'success-message') {
            swal("Good job!", "You clicked the button!", "success")

        } else if (type == 'warning-message-and-confirmation') {
            swal({
                title: "Are you sure?",
                text: "This item will be deleted!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-info btn-fill",
                confirmButtonText: "Yes, delete it!",
                cancelButtonClass: "btn btn-danger btn-fill",
                closeOnConfirm: false,
            }, function() {
                var f = document.createElement('form'); 
                f.style.display = 'none'; 
                this.parentNode.appendChild(f); 
                f.method = 'POST'; 
                f.action = this.href; 
                var m = document.createElement('input'); 
                m.setAttribute('type', 'hidden'); 
                m.setAttribute('name', '_method'); 
                m.setAttribute('value', 'DELETE'); 
                f.appendChild(m); 
                f.submit();
                swal("Deleted!", "The item has been deleted.", "success");
            });

        } else if (type == 'warning-message-and-cancel') {
            swal({
                title: "Are you sure?",
                text: "This item will be deletedsafd!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm) {
                if (isConfirm) {
                    var f = document.createElement('form'); 
                    f.style.display = 'none'; 
                    this.parentNode.appendChild(f); 
                    f.method = 'POST'; 
                    f.action = this.href; 
                    var m = document.createElement('input'); 
                    m.setAttribute('type', 'hidden'); 
                    m.setAttribute('name', '_method'); 
                    m.setAttribute('value', 'DELETE'); 
                    f.appendChild(m); 
                    f.submit();
                    swal("Deleted!", "The item has been deleted.", "success");
                } else {
                    swal("Cancelled", "The item is safe :)", "error");
                }
            });

        } else if (type == 'custom-html') {
            swal({
                title: 'HTML example',
                html: 'You can use <b>bold text</b>, ' +
                    '<a href="http://github.com">links</a> ' +
                    'and other HTML tags'
            });

        } else if (type == 'auto-close') {
            swal({
                title: "Auto close alert!",
                text: "I will close in 2 seconds.",
                timer: 2000,
                showConfirmButton: false
            });
        } else if (type == 'input-field') {
            swal({
                    title: 'Input something',
                    html: '<p><input id="input-field" class="form-control">',
                    showCancelButton: true,
                    closeOnConfirm: false,
                    allowOutsideClick: false
                },
                function() {
                    swal({
                        html: 'You entered: <strong>' +
                            $('#input-field').val() +
                            '</strong>'
                    });
                })
        }
    }

}

// JSON.parse(json) returns error when parsing html (error page, etc)
// https://stackoverflow.com/questions/29797946/handling-bad-json-parse-in-node-safely
function safelyParseJSON (json) {
  // This function cannot be optimised, it's best to
  // keep it small!
  var parsed

  try {
    parsed = JSON.parse(json)
  } catch (e) {
    // return nothing
  }

  return parsed // Could be undefined!
}



