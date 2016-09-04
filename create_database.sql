# Create database and tables for HappyQ
# Run as root:
#   mysql -u root -p < create_database.sql

begin;

create database HappyQ_DB;
use HappyQ_DB;

# macros can not be used in create table.
# So we have to repeate the sizes everywhere.

# Explicitly set character set to utf8 for all tables.

# The Resource table.
create table Resources (
  Name varchar(64) primary key,
  Custom varchar(256)
) default charset=utf8;

# The ResourceQueues table.
create table ResourceQueues (
  Name varchar(64) primary key,
  Cancelable bool not null default false
) default charset=utf8;

# The Queues table.
# QueueTime default to current timestamp and no automatically update.
create table Queues (
  RequestId int(8) primary key auto_increment,
  QueueTime timestamp default current_timestamp,
  UserId varchar(64) not null,
  ResourceName varchar(64) not null,
  Custom varchar(256),
  State varchar(32) not null default 'Wait'
) default charset=utf8;
create index Queues_ResourceStateUserIndex on Queues (
  ResourceName, State, UserId
);

# The Servings table.
# ServeTime default to current timestamp and no automatically update.
create table Servings (
  RequestId int(8) primary key,
  ResourceId int(8) not null,
  ServeTime timestamp default current_timestamp
) default charset=utf8;
create index Servings_ResourceIndex on Servings (ResourceId);

# The ResourcePool table.
# ProduceTime default to current timestamp and no automatically update.
create table ResourcePool (
  ResourceId int(8) primary key auto_increment,
  ProduceTime timestamp default current_timestamp,
  ResourceName varchar(64) not null,
  Custom varchar(256)
) default charset=utf8;
create index ResourcePool_ResourceIndex on ResourcePool (
  ResourceName
);

# Add user and grant privilege
create user 'happyq'@'localhost' identified by 'happyq';
grant select,insert,update,delete on HappyQ_DB.* to 'happyq'@'localhost';

commit;
