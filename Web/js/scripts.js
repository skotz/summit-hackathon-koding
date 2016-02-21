$(function(){ 
    var intervals = [];
    var needToLogIn = function() {
        location.reload();
    };

    // Fill the background of an element to a certain progress level
    var setProgress = function ($el, percent) {
        $color = "255, 255, 255";
        if ($el.hasClass("recording")) {
            $color = "255, 0, 0";
        }
        $el.css("background", "-webkit-linear-gradient(left, rgba(" + $color + ", 0.8) " + percent + "%, rgba(" + $color + ", 0.6) " + percent + "%)");
        $el.css("background", "-moz-linear-gradient(left, rgba(" + $color + ", 0.8) " + percent + "%, rgba(" + $color + ", 0.6) " + percent + "%)");
        $el.css("background", "-ms-linear-gradient(left, rgba(" + $color + ", 0.8) " + percent + "%, rgba(" + $color + ", 0.6) " + percent + "%)");
        $el.css("background", "linear-gradient(left, rgba(" + $color + ", 0.8) " + percent + "%, rgba(" + $color + ", 0.6) " + percent + "%)");
    };
    
    // Set up any events related to projects or tasks
    var initEvents = function () {
        $("[data-taskid]").click(function() {
            var thisTask = $(this);
            if (thisTask.hasClass('recording')) {
                // End the task
                $.ajax({
                    url: 'ajax.php',
                    type: 'post',
                    data: { 
                        'action': 'endlog', 
                        'taskid': thisTask.data("taskid"), 
                        'timelogend': new Date().toLocaleString().replace(',', '')
                    },
                    success: function(response) {
                        var data = $.parseJSON(response);
                        if (data.success) {
                            thisTask.removeClass('recording');
                            loadDash();
                        }
                    },
                    error: needToLogIn
                });  
            } else {
                // Start the task
                $.ajax({
                    url: 'ajax.php',
                    type: 'post',
                    data: { 
                        'action': 'startlog', 
                        'taskid': thisTask.data("taskid"), 
                        'timelogstart': new Date().toLocaleString().replace(',', '')
                    },
                    success: function(response) {
                        var data = $.parseJSON(response);
                        if (data.success) {
                            thisTask.addClass('recording');
                            loadDash();
                        }
                    },
                    error: needToLogIn
                });  
            }        
        });  
    };
    
    String.prototype.toHHMMSS = function () {
        // Adapted from http://stackoverflow.com/a/6313008
        var sec_num = parseInt(this, 10);
        var hours = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);
        if (hours < 10) { hours = "0" + hours; }
        if (minutes < 10) { minutes = "0" + minutes; }
        if (seconds < 10) { seconds = "0" + seconds; }
        return (hours != "00" ? hours + ':' : "") + minutes + ':' + seconds
    };
    
    // Create the markup for projects and tasks
    var loadDash = function () {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: { 
                'action': 'load',
                'date': new Date().toLocaleString().replace(',', '')
            },
            success: function(response) {
                var data = $.parseJSON(response);
                if (data.success) {
                    $(".page-login").hide();
                    $(".page-dash").fadeIn();
                    $("#task-project").html("");
                    
                    // Clear any intervals currently set for tracking currently recording time
                    var interval;
                    while (interval = intervals.pop()) {
                        clearInterval(interval);
                    }
                    
                    var markup = '';
                    var projects = 0;
                    for (var key in data.projects) {
                        if (data.projects.hasOwnProperty(key)) {
                            var proj = data.projects[key];
                            projects++;
                            
                            // Update the project dropdown for adding new tasks
                            $('#task-project').append($('<option>', { 
                                value: key,
                                text : proj.projectname 
                            }));
    
                            markup += "<div class='project' style='background: " + proj.projectcolor + "' data-projectid='" + key + "'>";
                            markup += "<h2>";
                            markup += proj.projectname; 
                            markup += "<div class='pull-right task-time'>";
                            markup += proj.totalprojecttime > 0 ? proj.totalprojecttime.toHHMMSS() : ""; 
                            markup += "</div>";
                            markup += "</h2>";
                            markup += "<div class='alltasks'>";
                            
                            for (var tkey in proj.tasks) {
                                if (proj.tasks.hasOwnProperty(tkey)) {
                                    var task = proj.tasks[tkey];
                                    
                                    // If we're recording, refresh the running time every second
                                    var partialTime = -1;
                                    if (task.recording) {
                                        for (var lkey in task.timelogs) {
                                            if (task.timelogs.hasOwnProperty(lkey)) {
                                                var log = task.timelogs[lkey];
                                                
                                                // Only update if we find a time log that's currently running
                                                var thisTask = task.taskid;
                                                var thisLogStart = log.timelogstart;
                                                if (log.timelogend.length == 0) {
                                                    partialTime = log.partialTime;
                                                    var updateTime = (function(thisTask, thisLogStart) { 
                                                        return function () {
                                                            console.log(thisTask);
                                                            var seconds = (new Date() - new Date(thisLogStart)) / 1000;
                                                            if (seconds < 0) {
                                                                // HACK for daylight savings... Figure out UTC parsing later
                                                                seconds += 60 * 60;
                                                            }
                                                            $("[data-taskid='" + thisTask + "'] .task-time").html(seconds.toString().toHHMMSS());
                                                        };
                                                    })(thisTask, thisLogStart);
                                                    intervals.push(setInterval(updateTime, 1000));
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Create the task markup
                                    if (task.taskname.length) {
                                        markup += "<div class='task " + (task.recording ? "recording" : "") + "' data-taskid='" + task.taskid + "' data-percent='" + (100 * task.totaltasktime / proj.totalprojecttime) + "'>";
                                        markup += task.taskname;
                                        if (task.recording && partialTime >= 0) {
                                            markup += "<div class='pull-right task-time'>" + partialTime.toHHMMSS() + "</div>";  
                                        } else {
                                            markup += "<div class='pull-right task-time'>";
                                            markup += task.totaltasktime > 0 ? task.totaltasktime.toHHMMSS() : "<em>Click to start recording!</em>"; 
                                            markup += "</div>";                                        
                                        }
                                        markup += "</div>";
                                    } else {
                                        // There are no tasks for this project, so create a helpful hint
                                        markup += "<div class='task hint'>";
                                        markup += "<em>Add tasks to this project using the Tasks panel.</em>";
                                        markup += "</div>";
                                    }
                                }
                            }
                            
                            markup += "</div>";
                            markup += "</div>";
    
                            //console.warn(proj);
                        }
                    }
                    
                    if (projects == 0) {
                        // The user doesn't have any projects yet, so show a helpful hint                    
                        markup += "<div class='project' style='background: #56cf54'>";
                        markup += "<h2>Add projects using the Projects panel to begin.</h2>";
                        markup += "<div class='alltasks'>";
                    }
                    
                    $(".all-projects").html(markup);
                    
                    // Set the progress backgrounds
                    $.each($("[data-taskid]"), function () {
                        setProgress($(this), $(this).data("percent"));                     
                    });
                    
                    initEvents();
                }
            }
        });
    };
    
    if ($(".page-dash:visible").length) {
        loadDash();
    }
   
    $("#login").click(function() {
        $(".login-errors").stop().hide();
        $(".login-success").stop().hide();
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: { 
                'action': 'login', 
                'username': $("#username").val(), 
                'password': $("#password").val()
            },
            success: function(response) {
                var data = $.parseJSON(response);
                if (data.success) {
                    $(".login-success").show();
                    $(".username").html($("#username").val());
                    loadDash();
                } else {
                    $(".login-errors .error-message").html("Invalid username or password.");
                    $(".login-errors").stop().fadeIn();
                }
            },
            error: function(xhr, desc, err) {
                $(".login-errors .error-message").html("Bummer, we're having server problems right now. Please try again later.");
                $(".login-errors").stop().fadeIn();
            }
        });        
    });
    
    $("#register").click(function() {
        $(".login-errors").stop().hide();
        $(".login-success").stop().hide();
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: { 
                'action': 'register', 
                'username': $("#username").val(), 
                'password': $("#password").val()
            },
            success: function(response) {
                var data = $.parseJSON(response);
                if (data.success) {
                    $(".login-success").show();
                    $(".username").html($("#username").val());
                    loadDash();
                } else {
                    $(".login-errors .error-message").html("Please select a different username.");
                    $(".login-errors").stop().fadeIn();
                }
            },
            error: function(xhr, desc, err) {
                $(".login-errors .error-message").html("Bummer, we're having server problems right now. Please try again later.");
                $(".login-errors").stop().fadeIn();
            }
        });        
    });
    
    $("#project-add").click(function() {
        $(".project-errors").stop().hide();
        $(".project-success").stop().hide();
        if ($("#project-name").val().length == 0) {
            $(".project-errors .error-message").html("Project name is required.");
            $(".project-errors").stop().fadeIn();
        } else if ($("#project-color").val().replace("#", "").length != 6) {
            $(".project-errors .error-message").html("Color must be a 6 character hexidecimal color code.");
            $(".project-errors").stop().fadeIn();
        } else {
            $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: { 
                    'action': 'createproject', 
                    'projectname': $("#project-name").val(), 
                    'projectcolor': $("#project-color").val().replace("#", "")
                },
                success: function(response) {
                    var data = $.parseJSON(response);
                    if (data.success) {
                        $("#project-name").val("");
                        $("#project-color").val("");
                        $(".project-success").stop().fadeIn().delay(5000).fadeOut();
                        loadDash();
                    } else {
                        $(".project-errors .error-message").html("Something went wrong...");
                        $(".project-errors").stop().fadeIn();
                    }
                },
                error: needToLogIn
            });
        }
    });
    
    $("#task-add").click(function() {
        $(".task-errors").stop().hide();
        $(".task-success").stop().hide();
        if ($("#task-name").val().length == 0) {
            $(".task-errors .error-message").html("Task name is required.");
            $(".task-errors").stop().fadeIn();
        } else if ($("#task-project").val().length == 0) {
            $(".task-errors .error-message").html("Project is required.");
            $(".task-errors").stop().fadeIn();
        } else {
            $.ajax({
                url: 'ajax.php',
                type: 'post',
                data: { 
                    'action': 'createtask', 
                    'taskname': $("#task-name").val(), 
                    'projectid': $("#task-project").val()
                },
                success: function(response) {
                    var data = $.parseJSON(response);
                    if (data.success) {
                        $("#task-name").val("");
                        $("#task-project").val("");
                        $(".task-success").stop().fadeIn().delay(5000).fadeOut();
                        loadDash();
                    } else {
                        $(".task-errors .error-message").html("Something went wrong...");
                        $(".task-errors").stop().fadeIn();
                    }
                },
                error: needToLogIn
            });     
        }   
    });
    
    
    $("#logout").click(function() {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: { 
                'action': 'logout'
            },
            success: function(response) {
                $("#username").val("");
                $("#password").val("");
                $(".page-dash").hide();
                $(".page-login").fadeIn();
            }
        }); 
    });    
    
    var setDefault = function (textbox, button) {
        $('#' + textbox).keypress(function (e) {
            if (e.keyCode == 13) {
                $('#' + button).click();            
            }
        });        
    };
    setDefault("username", "login");
    setDefault("password", "login");
    
    $("#project-color").colorpicker();
});