To let mysql fully use utf8, make the following setting in /etc/mysql/my.cnf.
Then restart mysql by 'sudo service mysql restart'.

[client]
default-character-set = utf8
[mysql]
default-character-set = utf8
[mysqld]
collation-server = utf8_general_ci
init-connect = 'SET NAMES utf8'
character-set-server = utf8
