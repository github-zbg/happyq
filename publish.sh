#!/bin/bash
# Copy script to /var/www/html to publish.

# Due to citc, we need a tmp dir as the intermediate.
temp_dir="/tmp/happyq";
rm $temp_dir -rf
mkdir -p $temp_dir
cp * $temp_dir -f


rm $temp_dir/publish.sh -f
rm $temp_dir/OWNERS -f
sudo cp $temp_dir/* /var/www/html/ -f

sudo chmod a+r /var/www/html/*
