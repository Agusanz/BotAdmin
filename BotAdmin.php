<?php
//Agusanz.com
require_once("libraries/TeamSpeak3/TeamSpeak3.php");
date_default_timezone_set('America/Argentina/Buenos_Aires'); //Change to your timezone. URL: https://secure.php.net/manual/en/timezones.php
TeamSpeak3::init();

//////////Config//////////
$user = ""; //Login user
$pass = ""; //Login password
$serverIP = "127.0.0.1"; //Server IP
$nickname = "BotAdmin"; //Bot Nickname
$BotChannelAFK = 1; //Channel where people will be moved to.
$TimeAFK = 1800; //Time that people will be allowed to stay afk until bot moves him/her. Use seconds.
$TimeKick = 3600; //Time that people will be allowed to stay afk until bot kick him/her when server is full. Use seconds.
//////////Config//////////

try
{
	$ts3 = TeamSpeak3::factory("serverquery://{$user}:{$pass}@{$serverIP}:10011/?server_port=9987&blocking=0&nickname={$nickname}"); //If you want to run this on another Query port or Voice port, change that here, otherwise leave it as it is.
	$statusKick = 0;
	$unixTime = time();
	$realTime = date('[Y-m-d] [H:i:s]',$unixTime);
	echo $realTime."\t[INFO] Connected\n"; //Log
	foreach($ts3->clientList() as $client)
	{
		if($client["client_type"]) continue; //Ignore query clients
		//AFK
		if($client["client_idle_time"]/1000 > $TimeAFK)
		{
			if($client["client_channel_group_inherited_channel_id"] != $BotChannelAFK) //Skip people already in the AFK Channel.
			{
				$unixTime = time();
				$realTime = date('[Y-m-d] [H:i:s]',$unixTime);
				$client->move($BotChannelAFK);
				$client->poke("Moved to AFK Channel because you has been AFK for too long."); //Poke message.
				echo $realTime."\t[AFK IDLE] ".$client["client_nickname"]." UID: ".$client["client_unique_identifier"]." IDLE: ".$client["client_idle_time"]/1000 ."\n"; //Log
			}
			else
			{
				echo $realTime."\t[AFK Already] ".$client["client_nickname"]." UID: ".$client["client_unique_identifier"]." IDLE: ".$client["client_idle_time"]/1000 ."\n"; //Log
			}
		}
		//AFK
		//AUTOKICK
		$unixTime = time();
		$realTime = date('[Y-m-d] [H:i:s]',$unixTime);
    	$serverInfo = $ts3->getInfo();
    	$maxClients = $serverInfo["virtualserver_maxclients"];
    	$clientsOnline = $serverInfo["virtualserver_clientsonline"];
    	$slotsReserved = $serverInfo["virtualserver_reserved_slots"];
    	$slotsAvailable = $maxClients - $slotsReserved;
    	$slotsNow = $slotsAvailable - $clientsOnline;
    	if($slotsNow < 1) //Check how many slots are available in the server, if it's less than 1, kick mode turns on.
    	{
    		$statusKick = 1;
			if(($client["client_channel_group_inherited_channel_id"] == $BotChannelAFK) && ($client["client_idle_time"]/1000 > $TimeKick)) //Bot kick people in the AFK channel only.
			{
				$client->kick(TeamSpeak3::KICK_SERVER, "Server is full and you has been AFK for too long."); //Kick message.
				echo $realTime."\t[KICK] ".$client["client_nickname"]." UID: ".$client["client_unique_identifier"]." IDLE: ".$client["client_idle_time"]/1000 ."\n"; //Log
			}
		}
		//AUTOKICK
	}
	$unixTime = time();
	$realTime = date('[Y-m-d] [H:i:s]',$unixTime);
	if($statusKick == 0)
	{
		echo $realTime."\t[KICK] Disabled. Slots: ".$clientsOnline."/".$slotsAvailable." Remaining: ".$slotsNow."\n"; //Log
	}
	else
	{
		echo $realTime."\t[KICK] Enabled. Slots: ".$clientsOnline."/".$slotsAvailable." Remaining: ".$slotsNow."\n"; //Log
	}
	die($realTime."\t[INFO] Finished.\n"); //Log
}
catch(Exception $e)
{
	$unixTime = time();
	$realTime = date('[Y-m-d] [H:i:s]',$unixTime);
	echo "Failed\n"; //Log
	die($realTime."\t[ERROR]  " . $e->getMessage() . "\n". $e->getTraceAsString() ."\n"); //Log
}
//Agusanz.com