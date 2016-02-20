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