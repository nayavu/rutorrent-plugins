<?php
require_once( dirname(__FILE__)."/../../php/xmlrpc.php" );
require_once( dirname(__FILE__)."/../../php/Torrent.php" );
eval( getPluginConf( 'changepassword' ) );

function crypt_apr1_md5($plainpasswd) {
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $len = strlen($plainpasswd);
    $text = $plainpasswd . '$apr1$' . $salt;
    $bin = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
    for ($i = $len; $i > 0; $i -= 16) {
        $text .= substr($bin, 0, min(16, $i));
    }
    for ($i = $len; $i > 0; $i >>= 1) {
        $text .= ($i & 1) ? chr(0) : $plainpasswd[0];
    }
    $bin = pack("H32", md5($text));
    $tmp = "";
    for( $i = 0; $i < 1000; $i++) {
        $new = ($i & 1) ? $plainpasswd : $bin;
        if ($i % 3) {
            $new .= $salt;
        }
        if ($i % 7) {
            $new .= $plainpasswd;
        }
        $new .= ($i & 1) ? $bin : $plainpasswd;
        $bin = pack("H32", md5($new));
    }
    for ($i = 0; $i < 5; $i++) {
        $k = $i + 6;
        $j = $i + 12;
        if ($j == 16) {
            $j = 5;
        }
        $tmp = $bin[$i] . $bin[$k] . $bin[$j]  .$tmp;
    }
    $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
    return "$"."apr1"."$".$salt."$".$tmp;
}

function do_crypt($plainpasswd) {
    return crypt($plainpasswd, base64_encode($plainpasswd));
}

$error = null;
if (empty($_SERVER["PHP_AUTH_USER"])) {
    $error = "Authentication is required";
} else if (empty($_POST["password"])) {
    $error = "Password is empty";
} else {
    if (empty($httpasswd_path) || !file_exists($httpasswd_path)) {
        $error = "Cannot find .htpasswd file, check configuration";
    } else {
        $username = $_SERVER["PHP_AUTH_USER"];
        $password = trim($_POST["password"]);
        
        $lines = explode("\n", trim(file_get_contents($httpasswd_path)));
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (substr($line, 0, strpos($line, ':')) === $username) {
                $lines[$i] = $username.':'.($encryption_method == "crypt" ? do_crypt($password) : crypt_apr1_md5($password));
            }
        }
        file_put_contents($httpasswd_path, implode("\n", $lines)."\n");
        
        $ret = ["errors" => [], "status" => 1];
    }
}

if (empty($ret)) {
	$ret = array("errors"=>array($error), "status"=>0);
}
cachedEcho(json_encode($ret), "application/json");