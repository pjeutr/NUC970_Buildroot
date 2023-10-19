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

    if ($("#userForm").length != 0) {
        $("#scan_key").click(function () {
            //$('.loaderImage').show();
            app.addSpinnerToButton(this, true);
            var self = this;

            $.ajax({
                //url: endpoint + "?key=" + apiKey + " &q=" + $( this ).text(),
                //contentType: "application/json",
                //dataType: 'json',
                url: "/?/last_scanned_key.json",
                success: function(result){
                    $("#user_keycode").val(result);
                    //$('.loaderImage').hide();
                    app.addSpinnerToButton(self, false);
                },
                error: function (response) {
                   //Handle error
                   //$('.loaderImage').hide();
                   app.addSpinnerToButton(self, false);
                }
            });
        });
    };


    if ($("#timezoneForm").length != 0) {
        //https://github.com/nikolasmagno/jquery-weekdays
        console.log("init timezoneForm");
        timezoneFormInit();
    };

    if ($(".settingsForm").length != 0) {
        console.log("init settingsForm");
        //$("#settingsForm").validate();
        settingsFormValidation();
    };

    // Init Datetimepicker
    if ($("#datetimepicker").length != 0) {
        $('.datetimepicker').datetimepicker({
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
            format: 'MM/DD/YYYY',
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

});

app = {
    //Button spinner
    addSpinnerToButton: function(button, showSpinner) {
        console.log("buttonSpinner="+showSpinner);
        button.disabled=showSpinner; 
        button.innerHTML=showSpinner ? '<i class="fa fa-spinner fa-spin"></i> Loading…':'Use scanned key';
        console.log(button);
    },

    // Sweet Alerts
    timerAlert: function(message, time, url) {
        $.ajax({
            //url: endpoint + "?key=" + apiKey + " &q=" + $( this ).text(),
            //contentType: "application/json",
            //dataType: 'json',
            url: url,
            success: function(result){
                console.log(result);
            }
        })
        swal({
            title: "Auto close alert!",
            text: message,
            timer: time,
            showConfirmButton: true
        });
    },    
    areYouSure: function(that) {
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
            that.parentNode.appendChild(f); 
            f.method = 'POST'; 
            f.action = that.href; 
            var m = document.createElement('input'); 
            m.setAttribute('type', 'hidden'); 
            m.setAttribute('name', '_method'); 
            m.setAttribute('value', 'DELETE'); 
            f.appendChild(m); 
            f.submit();
            swal("Deleted!", "The item has been deleted.", "success");
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





