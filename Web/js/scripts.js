$(function(){ 
    var intervals = [];
    var needToLogIn = function() {
        location.reload();
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
        // http://stackoverflow.com/a/6313008
        var sec_num = parseInt(this, 10);
        var hours   = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);

        if (hours   < 10) {hours   = "0"+hours;}
        if (minutes < 10) {minutes = "0"+minutes;}
        if (seconds < 10) {seconds = "0"+seconds;}
        var time    = hours+':'+minutes+':'+seconds;
        return time;
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
                    for (var key in data.projects) {
                        if (data.projects.hasOwnProperty(key)) {
                            var proj = data.projects[key];
                            
                            // Update the project dropdown for adding new tasks
                            $('#task-project').append($('<option>', { 
                                value: key,
                                text : proj.projectname 
                            }));
    
                            markup += "<div class='project' style='background: " + proj.projectcolor + "' data-projectid='" + key + "'>";
                            markup += "<h2>" + proj.projectname + "</h2>";
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
                                                    var updateTime = function () {
                                                        var seconds = (new Date() - new Date(thisLogStart)) / 1000;
                                                        $("[data-taskid='" + thisTask + "'] .task-time").html(seconds.toString().toHHMMSS());
                                                    };
                                                    intervals.push(setInterval(updateTime, 1000));
                                                }
                                            }
                                        }
                                    }
                                    
                                    markup += "<div class='task " + (task.recording ? "recording" : "") + "' data-taskid='" + task.taskid + "'>";
                                    markup += task.taskname;
                                    console.log(partialTime)
                                    if (task.recording && partialTime >= 0) {
                                        markup += "<div class='pull-right task-time'>" + partialTime.toHHMMSS() + "</div>";  
                                    } else {
                                        markup += "<div class='pull-right task-time'>" + task.totaltasktime.toHHMMSS() + "</div>";                                        
                                    }
                                    markup += "</div>";
                                }
                            }
                            
                            markup += "</div>";
                            markup += "</div>";
    
                            console.warn(proj);
                        }
                    }
                    
                    $(".all-projects").html(markup);
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
                    loadDash();
                }
            },
            error: needToLogIn
        });        
    });
    
    $("#task-add").click(function() {
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
                    loadDash();
                }
            },
            error: needToLogIn
        });        
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