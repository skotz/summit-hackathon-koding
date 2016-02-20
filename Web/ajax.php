<?php
require_once("lib/debug.php");
require_once("lib/user.php");
require_once("lib/mysql.php");
require_once("lib/crypto.php");

if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case "logout":
            $_SESSION["username"] = "";
            break;
            
        case "login":
            if (isset($_POST['username']) && isset($_POST['password'])) 
            {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $success = loginUser($username, $password);
                
                if ($success) 
                {
                    $_SESSION["username"] = $username;
                }
                
                echo "{ \"success\": " . ($success ? "true" : "false") . " }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "register":
            if (isset($_POST['username']) && isset($_POST['password']) && !usernameExists($_POST['username'])) 
            {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $salt = random_str(100);
                $hashedpass = $password . $salt;
                
                $sql = $db->prepare('insert into webapp.users (username, password, passwordsalt) values (?, sha1(?), ?)');
                $sql->bind_param('sss', $username, $hashedpass, $salt);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "load":
            if ($user != "") 
            {
                $projects = "";
                
                $sql = $db->prepare("
                    select projects.projectid,
                           projects.projectname,
                           projects.projectcolor,
                           tasks.taskid,
                           tasks.taskname,
                           timelog.timelogid,
                           timelog.timelogstart,
                           timelog.timelogend
                      from webapp.projects
                      left
                     outer
                      join webapp.tasks
                        on projects.projectid = tasks.projectid
                      left
                     outer
                      join webapp.timelog
                        on tasks.taskid = timelog.taskid
                     where projects.username = ?
                ");
                $sql->bind_param("s", $user);
                $sql->execute();
                
                $sql->bind_result($projectid, $projectname, $projectcolor, $taskid, $taskname, $timelogid, $timelogstart, $timelogend);
                $projects .= "{";
                $numprojs = 0;
                while ($sql->fetch())
                {
                    if ($numprojs > 0)
                    {
                        $projects .= ",";
                    }
                    $projects .= "\"" . $projectid . "\": {";
                    $projects .= "\"projectname\":\"" . $projectname . "\", ";
                    $projects .= "\"projectcolor\":\"#" . $projectcolor . "\"";
                    $projects .= "} ";
                    $numprojs++;
                }
                $projects .= "}";
                
                $sql->close();
    
                echo "{ \"success\": true, \"projects\": " . $projects . " }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "createproject":
            if ($user != "") 
            {
                $projectname = $_POST['projectname'];
                $projectcolor = $_POST['projectcolor'];
                
                $sql = $db->prepare('insert into webapp.projects (username, projectname, projectcolor) values (?, ?, ?)');
                $sql->bind_param('sss', $user, $projectname, $projectcolor);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        default:
            echo "{ \"success\": false }";
            break;
    }
}

?>