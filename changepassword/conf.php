<?php

$httpasswd_path = dirname(dirname(dirname(dirname(__FILE__)))).'/.htpasswd'; // Location of .htpasswd

$encryption_method = "crypt"; //one of 'crypt' (if users to htpasswd were added with '-d', userful for vsftpd support)  or 'default'