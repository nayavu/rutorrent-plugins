<?php

// if ftp is anonymous and downloaded torrents ara available in /downloads
//$ftp_link = "ftp://127.0.0.1/downloads";

// if ftp is not anonymous with per-user access 
$ftp_link = sprintf("ftp://%s:%s@127.0.0.1/downloads", $_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]);


// Base directory for FTP server
// 
// Example:
// for current user, downloaded data is placed in user downloads dir = /home/rtorrent/data/{%username}/downloads
// and these downloads is available at $ftp_link = ftp://username:password@127.0.0.1/downloads
// then $ftp_link should be exactly thte same as user downloads dir:
// {result ftp url} = $ftp_link + {torrent downloads path}.replace($ftp_base_directory, '')

$ftp_base_directory = sprintf(addslash(rTorrentSettings::get()->directory).'%s/downloads', $_SERVER["PHP_AUTH_USER"]); 