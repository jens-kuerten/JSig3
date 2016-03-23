# JSig3 #

##Installation##
###JSig###
* populate database with the provided sql data dump
* setup database connection in *server/config/secrets.ini*
* setup whitelist in *server/config/config.ini*
###EVE-Single Sign On###
* register App at https://developers.eveonline.com/
* edit the clientID and secret in *server/config/secrets.ini*
* edit the callbackUrl in *server/config/config.ini* (e.g. http://www.team-veldspar.de/jsig3/)
* add the callback url to your App at https://developers.eveonline.com/ (e.g. http://www.team-veldspar.de/jsig3/callback.php)

###Minimum-requirements###
* php 5.4 
* mysql 5.1 (not tested on lower versions/other databases, but might work)