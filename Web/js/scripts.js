$(function(){    
    $("#login").click(function() {
        $(".login-errors").stop().hide();
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
                    $(".page-login").hide();
                    $(".page-dash").fadeIn();
                    $(".username").html($("#username").val());
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
                    $(".page-login").hide();
                    $(".page-dash").fadeIn();
                    $(".username").html($("#username").val());
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
});