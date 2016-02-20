<?php
require_once("lib/user.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Skotz Time Tracker</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='css/styles.css' rel='stylesheet' type='text/css'>
    <link href='css/bootstrap-colorpicker.min.css' rel='stylesheet' type='text/css'>
    <style>
    <?php
        if ($user != "")
        {
            echo ".page-login { display: none; }";
        }
        else
        {
            echo ".page-dash { display: none; }";
        }
    ?>
    </style>
</head>
<body>
    <div class="container page-login">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h1>Skotz Time Tracker</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default login">
                    <div class="panel-heading">Log In or Register</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="alert alert-danger login-errors" role="alert">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Oops!</span>
                                    <span class="error-message"></span>
                                </div>
                                <div class="alert alert-success login-success" role="alert">
                                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                    <span class="sr-only">Yay!</span>
                                    Success!
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon" id="username-label">Username</span>
                                    <input type="text" class="form-control" id="username" aria-describedby="username-label">
                                </div>
                            </div>
                        </div>
                        <div class="row row-buff">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon" id="password-label">Password</span>
                                    <input type="password" class="form-control" id="password" aria-describedby="password-label">
                                </div>
                            </div>
                        </div>
                        <div class="row row-buff">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-success pull-right" id="login" aria-label="Left Align">
                                    Log In
                                </button>
                                <button type="button" class="btn btn-default pull-right register" id="register" aria-label="Left Align">
                                    Register
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container page-dash">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h1>Hey <span class="username"><?= $user ?></span>! <a href="javascript: void(0)" id="logout" class="btn btn-default pull-right">Sign Out</a></h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 all-projects">
            </div>
            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Projects</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon" id="project-name-label">Name</span>
                                    <input type="text" class="form-control" id="project-name" aria-describedby="project-name-label">
                                </div>
                            </div>
                        </div>
                        <div class="row row-buff">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon" id="project-color-label">Color</span>
                                    <input type="text" class="form-control" id="project-color" aria-describedby="project-color-label">
                                </div>
                            </div>
                        </div>
                        <div class="row row-buff">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-default pull-right" id="project-add" aria-label="Left Align">
                                    Create
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="//code.jquery.com/jquery-2.2.0.min.js"></script>
    <script src="js/bootstrap-colorpicker.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
