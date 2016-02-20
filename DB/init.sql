create user 'hackathon'@'localhost' identified by 'hackathon';
create database webapp;
grant all on webapp.* to 'hackathon'@'localhost';