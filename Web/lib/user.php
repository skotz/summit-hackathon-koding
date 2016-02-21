<?php
session_start(); 
require_once(__DIR__ . "/debug.php");
require_once(__DIR__ . "/mysql.php");
require_once(__DIR__ . "/crypto.php");

// Check whether a username exists in the system
function usernameExists($username)
{
    global $db;
    $success = false;
    $sql = $db->prepare("select count(*) as success from webapp.users where username = ?");
    $sql->bind_param("s", $username);
    $sql->execute();
    
    $sql->bind_result($token);            
    if ($sql->fetch())
    {
        $success = $token == 1;
    }
    
    $sql->close();
    return $success;
}

// Verify that a username and password is correct
function loginUser($username, $password)
{
    global $db;
    $success = false;    
    $sql = $db->prepare("select count(*) as success from webapp.users where username = ? and password = sha1(concat(?, passwordsalt))");
    $sql->bind_param("ss", $username, $password);
    $sql->execute();
    
    $sql->bind_result($token);            
    if ($sql->fetch())
    {
        $success = $token == 1;
    }
    
    $sql->close();
    return $success;
}

// Verify that a project is associated with a given user
function projectBelongsToUser($projectid, $username)
{
    global $db;
    $success = false;    
    $sql = $db->prepare("select count(*) from webapp.projects where username = ? and projectid = ?");
    $sql->bind_param("ss", $username, $projectid);
    $sql->execute();    
    $sql->bind_result($token);            
    if ($sql->fetch())
    {
        $success = $token == 1;
    }
    $sql->close();
    return $success;
}

// Verify that a task is associated with a given user
function taskBelongsToUser($taskid, $username)
{
    global $db;
    $success = false;    
    $sql = $db->prepare("
        select count(*) 
          from webapp.projects 
          join webapp.tasks
            on projects.projectid = tasks.projectid
         where projects.username = ? 
           and tasks.taskid = ?
    ");
    $sql->bind_param("ss", $username, $taskid);
    $sql->execute();    
    $sql->bind_result($token);            
    if ($sql->fetch())
    {
        $success = $token == 1;
    }
    $sql->close();
    return $success;
}

// Get the username of the logged in user
$user = "";
if (isset($_SESSION["username"]) && strlen($_SESSION["username"]) > 0)
{
    $user = $_SESSION["username"];
}

?>