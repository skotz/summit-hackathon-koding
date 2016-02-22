select projects.projectid,
       projects.projectname,
       projects.projectcolor,
       tasks.taskid,
       tasks.taskname,
       timelog.timelogid,
       date_format(timelog.timelogstart, '%c/%e/%Y %r') as timelogstart,
       date_format(timelog.timelogend, '%c/%e/%Y %r') as timelogend,
       timestampdiff(second, timelog.timelogstart, timelog.timelogend) as totalTime,
       timestampdiff(second, timelog.timelogstart, str_to_date(?, '%c/%e/%Y %r')) as partialTime,
       (select sum(timestampdiff(second, l2.timelogstart, l2.timelogend))
          from webapp.tasks t2
          left
         outer
          join webapp.timelog l2
            on t2.taskid = l2.taskid
         where l2.timelogend is not null
           and l2.timelogstart is not null
           and t2.projectid = projects.projectid) as totalprojecttime,
       (select sum(timestampdiff(second, l2.timelogstart, l2.timelogend))
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