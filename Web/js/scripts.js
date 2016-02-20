$(function(){ 
    var needToLogIn = function() {
        location.reload();
    };

    var loadDash = function () {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            data: { 
                'action': 'load'
            },
            success: function(response) {
                var data = $.parseJSON(response);
                if (data.success) {
                    $(".page-login").hide();
                    $(".page-dash").fadeIn();
                    $(".all-projects").html("");
                    
                    for (var key in data.projects) {
                        if (data.projects.hasOwnProperty(key)) {
                            var proj = data.projects[key];
                            
                            $(".all-projects").append("<div class='project' style='background: " + proj.projectcolor + "'>" +
                                "<h2>" + proj.projectname + "</h2>" + 
                                "</div>");
                            
                            console.warn(proj);
                        }
                    }
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