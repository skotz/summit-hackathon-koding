<?php
require_once("lib/debug.php");
require_once("lib/user.php");
require_once("lib/mysql.php");
require_once("lib/crypto.php");

class Project
{
    public $projectid;
    public $projectname;
    public $projectcolor;
    public $totalprojecttime;
    public $tasks;
}
class Task
{
    public $taskid;
    public $taskname;
    public $timelogs;
    public $totaltasktime;
    public $isrecording;
}
class TimeLog
{
    public $timelogid;
    public $timelogstart;
    public $timelogend;
    public $totalTime;
    public $partialTime;
}

if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case "logout":
            $_SESSION["username"] = "";
            break;
            
        case "login":
            if (isset($_POST['username']) && isset($_POST['password']) &&
                strlen($_POST['username']) > 0 && strlen($_POST['password']) > 0) 
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
            if (isset($_POST['username']) && isset($_POST['password']) && 
                strlen($_POST['username']) > 0 && strlen($_POST['password']) > 0 &&
                !usernameExists($_POST['username'])) 
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
            if ($user != "" && isset($_POST["date"])) 
            {
                $date = $_POST["date"];
                $allTime = 0;
                $sql = $db->prepare("
                    select projects.projectid,
                           projects.projectname,
                           projects.projectcolor,
                           tasks.taskid,
                           tasks.taskname,
                           timelog.timelogid,
                           date_format(timelog.timelogstart, '%c/%e/%Y %r') as timelogstart,
                           date_format(timelog.timelogend, '%c/%e/%Y %r') as timelogend,
                           timelog.timelogend - timelog.timelogstart as totalTime,
                           timestampdiff(second, timelog.timelogstart, str_to_date(?, '%c/%e/%Y %r')) as partialTime,
                           (select sum(l2.timelogend - l2.timelogstart)
                              from webapp.tasks t2
                              left
                             outer
                              join webapp.timelog l2
                                on t2.taskid = l2.taskid
                             where l2.timelogend is not null
                               and l2.timelogstart is not null
                               and t2.projectid = projects.projectid) as totalprojecttime,
                           (select sum(l2.timelogend - l2.timelogstart)
                              from webapp.tasks t2
                              left
                             outer
                              join webapp.timelog l2
                                on t2.taskid = l2.taskid
                             where l2.timelogend is not null
                               and l2.timelogstart is not null
                               and t2.taskid = tasks.taskid
                               and t2.projectid = projects.projectid) as totaltasktime,
                           (select count(*)
                              from webapp.projects p2
                              left
                             outer
                              join webapp.tasks t2
                                on p2.projectid = t2.projectid
                              left
                             outer
                              join webapp.timelog l2
                                on t2.taskid = l2.taskid
                             where l2.timelogend is null
                               and l2.timelogstart is not null
                               and p2.username = projects.username
                               and p2.projectid = projects.projectid
                               and t2.taskid = tasks.taskid) as isrecording
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
                $sql->bind_param("ss", $date, $user);
                $sql->execute();
                
                $sql->bind_result($projectid, $projectname, $projectcolor, $taskid, $taskname, $timelogid, $timelogstart, $timelogend, $totaltime, $partialTime, $totalprojecttime, $totaltasktime, $recording);
                $projects = array();
                while ($sql->fetch())
                {
                    // See if we already have this project in the list
                    $found = false;
                    foreach ($projects as $proj)
                    {
                        if ($proj->projectid == $projectid)
                        {
                            // See if this project already has this task in the list
                            $foundtask = false;
                            foreach ($proj->tasks as $task)
                            {
                                if ($task->taskid == $taskid)
                                {
                                    $log = new TimeLog();
                                    $log->timelogid = $timelogid;
                                    $log->timelogstart = $timelogstart;
                                    $log->timelogend = $timelogend;
                                    $log->partialTime = $partialTime;
                                    $log->totalTime = $totaltime;
                                    
                                    array_push($task->timelogs, $log);
                                    
                                    $foundtask = true;
                                    break;
                                }
                            }
                            
                            // Add a new task to the project
                            if (!$foundtask) 
                            {
                                $task = new Task();
                                $task->taskid = $taskid;
                                $task->taskname = $taskname;
                                $task->isrecording = $recording;
                                $task->totaltasktime = $totaltasktime;
                                $task->timelogs = array();
                                
                                $log = new TimeLog();
                                $log->timelogid = $timelogid;
                                $log->timelogstart = $timelogstart;
                                $log->timelogend = $timelogend;
                                $log->partialTime = $partialTime;
                                $log->totalTime = $totaltime;
                                
                                array_push($task->timelogs, $log);
                                array_push($proj->tasks, $task);
                            }
                            
                            $found = true;
                            break;
                        }
                    }
                    
                    // Add a new project to the list
                    if (!$found)
                    {
                        $proj = new Project();
                        $proj->projectid = $projectid;
                        $proj->projectname = $projectname;
                        $proj->projectcolor = $projectcolor;
                        $proj->totalprojecttime = $totalprojecttime;
                        $proj->tasks = array();
                        
                        $task = new Task();
                        $task->taskid = $taskid;
                        $task->taskname = $taskname;
                        $task->isrecording = $recording;
                        $task->totaltasktime = $totaltasktime;
                        $task->timelogs = array();
                        
                        $log = new TimeLog();
                        $log->timelogid = $timelogid;
                        $log->timelogstart = $timelogstart;
                        $log->timelogend = $timelogend;
                        $log->partialTime = $partialTime;
                        $log->totalTime = $totaltime;
                        
                        array_push($task->timelogs, $log);
                        array_push($proj->tasks, $task);
                        array_push($projects, $proj);
                        
                        $allTime += $totalprojecttime;
                    }                    
                }                
                $sql->close();
                
                $stringproj = "{";
                foreach ($projects as $proj)
                {
                    $stringproj .= "\"" . $proj->projectid . "\": {";
                    $stringproj .= "\"projectname\":\"" . $proj->projectname . "\",";
                    $stringproj .= "\"projectcolor\":\"#" . $proj->projectcolor . "\",";
                    $stringproj .= "\"totalprojecttime\":\"" . $proj->totalprojecttime . "\",";
                    $stringproj .= "\"alltime\":\"" . $allTime . "\",";
                    $stringproj .= "\"tasks\": {";
                    foreach ($proj->tasks as $task)
                    {
                        $stringproj .= "\"" . $task->taskid . "\": {";
                        $stringproj .= "\"taskid\":\"" . $task->taskid . "\",";
                        $stringproj .= "\"taskname\":\"" . $task->taskname . "\",";
                        $stringproj .= "\"totaltasktime\":\"" . $task->totaltasktime . "\",";
                        $stringproj .= "\"recording\":" . ($task->isrecording != "0" ? "true" : "false") . ",";
                        $stringproj .= "\"timelogs\": {";                        
                        foreach ($task->timelogs as $log)
                        {
                            $stringproj .= "\"" . $log->timelogid . "\": {";
                            $stringproj .= "\"timelogstart\":\"" . $log->timelogstart . "\",";
                            $stringproj .= "\"timelogend\":\"" . $log->timelogend . "\",";
                            $stringproj .= "\"totalTime\":\"" . $log->totalTime . "\",";
                            $stringproj .= "\"partialTime\":\"" . $log->partialTime . "\",";
                            $stringproj .= "},";
                        }
                        $stringproj .= "}},";
                    }
                    $stringproj .= "}},";
                }
                $stringproj .= "}";
                $stringproj = str_replace(",}", "}", $stringproj);
    
                echo "{ \"success\": true, \"projects\": " . $stringproj . " }";
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
            
        case "updateproject":
            if ($user != "" && isset($_POST['projectid']) && isset($_POST['projectname']) && isset($_POST['projectcolor']) && projectBelongsToUser($_POST['projectid'], $user)) 
            {
                $projectid = $_POST['projectid'];
                $projectname = $_POST['projectname'];
                $projectcolor = $_POST['projectcolor'];
                
                $sql = $db->prepare('update webapp.projects set projectname = ?, projectcolor = ? where username = ? and projectid = ?');
                $sql->bind_param('ssss', $projectname, $projectcolor, $user, $projectid);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "deleteproject":
            if ($user != "" && isset($_POST['projectid'])) 
            {
                $projectid = $_POST['projectid'];
                
                $sql = $db->prepare('delete from webapp.projects where username = ? and projectid = ?');
                $sql->bind_param('ss', $user, $projectid);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "createtask":
            if ($user != "" && isset($_POST['taskname']) && isset($_POST['projectid']) && projectBelongsToUser($_POST['projectid'], $user)) 
            {
                $taskname = $_POST['taskname'];
                $projectid = $_POST['projectid'];
                
                $sql = $db->prepare('insert into webapp.tasks (projectid, taskname) values (?, ?)');
                $sql->bind_param('ss', $projectid, $taskname);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "updatetask":
            if ($user != "" && isset($_POST['taskname']) && isset($_POST['taskid']) && taskBelongsToUser($_POST['taskid'], $user)) 
            {
                $taskname = $_POST['taskname'];
                $taskid = $_POST['taskid'];
                
                $sql = $db->prepare('update webapp.tasks set taskname = ? where taskid = ?');
                $sql->bind_param('ss', $taskname, $taskid);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "deletetask":
            if ($user != "" && isset($_POST['taskid']) && taskBelongsToUser($_POST['taskid'], $user)) 
            {
                $taskid = $_POST['taskid'];
                
                $sql = $db->prepare('delete from webapp.tasks where taskid = ?');
                $sql->bind_param('s', $taskid);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "startlog":
            if ($user != "" && isset($_POST['taskid']) && isset($_POST['timelogstart']) && taskBelongsToUser($_POST['taskid'], $user)) 
            {
                $taskid = $_POST['taskid'];
                $timelogstart = $_POST['timelogstart'];
                                
                $sql = $db->prepare("insert into webapp.timelog (taskid, timelogstart, timelogend) values (?, str_to_date(?, '%c/%e/%Y %r'), null)");
                $sql->bind_param('ss', $taskid, $timelogstart);
                $sql->execute();

                echo "{ \"success\": true }";
            }
            else
            {
                echo "{ \"success\": false }";
            }
            break;
            
        case "endlog":
            if ($user != "" && isset($_POST['taskid']) && isset($_POST['timelogend']) && taskBelongsToUser($_POST['taskid'], $user))
            {
                $taskid = $_POST['taskid'];
                $timelogend = $_POST['timelogend'];
                                
                $sql = $db->prepare("update webapp.timelog set timelogend = str_to_date(?, '%c/%e/%Y %r') where taskid = ? and timelogend is null");
                $sql->bind_param('ss', $timelogend, $taskid);
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