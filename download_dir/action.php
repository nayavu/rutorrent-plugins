<?php
require_once('../../php/rtorrent.php');
require_once( '../../php/xmlrpc.php' );

if (isset($_REQUEST['result'])) {
    cachedEcho('noty(theUILang.cantFindTorrent,"error");', "text/html");
}

$useZip = $_REQUEST['format'] == "zip";

function createZipStream($path, &$pipes) {
    $name = basename($path);
    $dir = dirname($path);

    header("Content-Type: application/x-zip");
    header("Content-Disposition: attachment; filename=\"$name.zip\"");

    return proc_open(
        "/usr/bin/zip -q -r - '$name'",
        [
            0 => array("pipe", "r"),
            1 => array("pipe", "w")
        ],
        $pipes,
        $dir);
}

function createTarStream($path, &$pipes) {
    $name = basename($path);
    $dir = dirname($path);

    header("Content-Type: application/x-tar");
    header("Content-Disposition: attachment; filename=\"$name.tar\"");

    return proc_open(
        "tar -cf - '$name'",
        [
            0 => array("pipe", "r"),
            1 => array("pipe", "w")
        ],
        $pipes,
        $dir);
}

if (isset($_REQUEST['hash'])) {
    $hash = $_REQUEST['hash'];

    $req = new rXMLRPCRequest([
        new rXMLRPCCommand("d.open", $hash),
        new rXMLRPCCommand("d.get_base_path", $hash),
        new rXMLRPCCommand("d.close", $hash)
    ]);

    $path = null;
    if ($req->success()) {
        $path = $req->val[1];
    }

    if (!$path || !is_dir($path)) {
        cachedEcho('noty("Directory not found.","error");', "text/html");
        exit;
    }

    if ($useZip) {
        $process = createZipStream($path, $pipes);
    } else {
        $process = createTarStream($path, $pipes);
    }

    if (!is_resource($process)) {
        cachedEcho('noty("Could not compress directory.","error");', "text/html");
        exit;
    }
    try {
        fpassthru($pipes[1]);
    } finally {
        fclose($pipes[1]);
        proc_close($process);
    }
    exit();
}
header("HTTP/1.0 302 Moved Temporarily");
header("Location: action.php?result=0");
