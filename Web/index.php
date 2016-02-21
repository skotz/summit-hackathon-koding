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
    <link rel="apple-touch-icon" sizes="57x57" href="/ico/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/ico/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/ico/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/ico/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/ico/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/ico/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/ico/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/ico/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/ico/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="/ico/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/ico/favicon-194x194.png" sizes="194x194">
    <link rel="icon" type="image/png" href="/ico/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/ico/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="/ico/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/ico/manifest.json">
    <link rel="mask-icon" href="/ico/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/ico/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-TileImage" content="/ico/mstile-144x144.png">
    <meta name="msapplication-config" content="/ico/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
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
                    <div class="row">
                        <div class="col-sm-8">
                            <h1>Hey <span class="username"><?= $user ?></span>!</h1>
                        </div>
                        <div class="col-sm-4 col-menu">
                            <a href="javascript: void(0)" id="logout" class="btn btn-default pull-right">Sign Out</a>
                            <a href="javascript: void(0)" id="view-task" class="btn btn-primary pull-right">%</a>
                        </div>
                    </div>
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
                                <div class="alert alert-danger project-errors" role="alert">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Oops!</span>
                                    <span class="error-message"></span>
                                </div>
                                <div class="alert alert-success project-success" role="alert">
                                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                    <span class="sr-only">Yay!</span>
                                    Project successfully added!
                                </div>
                            </div>
                        </div>
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
                <div class="panel panel-default">
                    <div class="panel-heading">Tasks</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="alert alert-danger task-errors" role="alert">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                    <span class="sr-only">Oops!</span>
                                    <span class="error-message"></span>
                                </div>
                                <div class="alert alert-success task-success" role="alert">
                                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                    <span class="sr-only">Yay!</span>
                                    Task successfully added!
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon" id="task-name-label">Name</span>
                                    <input type="text" class="form-control" id="task-name" aria-describedby="task-name-label">
                                </div>
                            </div>
                        </div>
                        <div class="row row-buff">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon" id="project-id-label">Project</span>
                                    <select class="form-control" id="task-project" aria-describedby="project-id-label">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row row-buff">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-default pull-right" id="task-add" aria-label="Left Align">
                                    Create
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2016 Scott Clayton &bull; 
        <a href="https://github.com/skotz/summit-hackathon-koding" target="_blank">GitHub</a> &bull; 
        <a href="https://www.koding.com/Hackathon" target="_blank">Virtual Hackathon</a>
    </footer>
    <script src="//code.jquery.com/jquery-2.2.0.min.js"></script>
    <script src="js/bootstrap-colorpicker.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
