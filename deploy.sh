#!/bin/bash

chmod a+r *

excludes="--exclude=OWNERS --exclude=deploy.sh --exclude=publish.sh "

tar -czvf happyq.tar.gz * $excludes

server="demo@138.68.22.29"
server_dir="/home/demo/happyq"

scp happyq.tar.gz $server:$server_dir

# use -t to open a virtual tty, required by sudo.
remote_commands=\
"sudo rm /var/www/html/* -f;"\
"sudo tar -xzvf $server_dir/happyq.tar.gz --directory=/var/www/html;"

ssh $server -t "$remote_commands"

rm -f happyq.tar.gz
