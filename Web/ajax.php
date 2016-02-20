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
            
        default:
            echo "{ \"success\": false }";
            break;
    }
}

?>