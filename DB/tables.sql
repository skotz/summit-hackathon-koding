create table users (
    username varchar(200),
    password varchar(100),
    passwordsalt varchar(200)
);

create table projects (
    projectid int not null auto_increment primary key,
    username varchar(200),
    projectname varchar(200),
    projectcolor varchar(6)
);

create table tasks (
    taskid int not null auto_increment primary key,
    projectid int,
    taskname varchar(200)
);

create table timelog (
    timelogid int not null auto_increment primary key,
    taskid int,
    timelogstart datetime,
    timelogend datetime
);