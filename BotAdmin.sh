#!/bin/bash

BotAdmin=`ps ax | grep BotAdmin.php | grep -v grep | wc -l`

if [ $BotAdmin -eq 1 ]
then
	exit
else
	cd /home/agusanz/TSBots/BotAdmin;
#	if [ -f BotAdmin.log ];
#	    then
#	        mv BotAdmin.log  logs/BotAdmin_$(date +"%Y-%m-%d_%H-%M-%S").log
#	    fi
	nohup php -f BotAdmin.php &> BotAdmin.log
fi
