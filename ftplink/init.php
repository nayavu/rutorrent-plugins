<?php
eval(getPluginConf($plugin["name"]));
$theSettings->registerPlugin($plugin["name"],$pInfo["perms"]);

$jResult.=('plugin.ftpLink="'.addslash($ftp_link).'";');
$jResult.=('plugin.ftpBaseDirectory="'.addslash($ftp_base_directory).'";');