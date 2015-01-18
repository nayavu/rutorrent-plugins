<?php


function get_user_dir() {
//    require ( dirname(__FILE__)."/conf.php" );
    $directory_per_user = "%s/downloads";
    return fullpath(sprintf($directory_per_user, $_SERVER["PHP_AUTH_USER"]), rTorrentSettings::get()->directory);
}

class XmlRpcRequestContext {
    public $methodName;
    public $req;
    
    public function __construct($methodName, $req) {
        $this->methodName = $methodName;
        $this->req = $req;
    }
}


function before_send(&$request) {
    $req = new SimpleXMLElement($request);
        
    $defaultDownloadDir = rTorrentSettings::get()->directory;
    $userDownloadDir = get_user_dir();
    $username = $_SERVER["PHP_AUTH_USER"];
            
    $methodName = $req->methodName;
    $context = null;
    if (strpos($methodName, "load") === 0) {
        replace_download_dir_and_set_username_on_load_command($req, $username, $defaultDownloadDir, $userDownloadDir);
        $request = $req->saveXML();
    } else if ($methodName == "system.multicall") {
        replace_download_dir_and_set_username_on_load_multicommand($req, $username, $defaultDownloadDir, $userDownloadDir);
        $request = $req->saveXML();
    } else if ($methodName == "d.multicall") {
        append_username_request_to_get_all_command($req);
        $request = $req->saveXML();
        
        $context = 'd.multicall';
    }
    
    return $context;
}

function after_send(&$response, $context) {
    $username = $_SERVER["PHP_AUTH_USER"];
    
    if ($context != null) {
        
        $pos = strpos($response, '<?');
        if ($pos !== false) {
            $beforeXml = substr($response, 0, $pos);

            $res = new SimpleXMLElement(substr($response, $pos));
            if ($context == 'd.multicall') {
                // last param should be username
                filter_by_username_get_all_command($res, $username);

            }
            $response = $beforeXml.$res->asXml();
        }
    }
}



///////////////////////////////////////////////
// load command 
$d_set_directory_part = 'd.set_directory=';
function append_username_to_params(SimpleXMLElement $params, $username) {
    $params->addChild('value')->addChild('string', "d.set_custom=x-username,$username");
}
function generate_new_download_directory($dir, $defaultDownloadDir, $userDownloadDir) {
    if (strpos($dir, $defaultDownloadDir) === 0) {
        // simply replace default_download_dir to user's one
        return str_replace($defaultDownloadDir, $userDownloadDir, $dir);
    } else {
        return $userDownloadDir;
    }
}


function replace_download_dir_and_set_username_on_load_command(SimpleXMLElement $req, $username, $defaultDownloadDir, $userDownloadDir) {
    append_username_to_params($req->params->addChild('param'), $username);
    
    global $d_set_directory_part;
    if (($dSetDirectory = $req->params->param->xpath('value/string[starts-with(text(), "'.$d_set_directory_part.'")]')) != null) {
        $dir = substr((string) $dSetDirectory[0], strlen($d_set_directory_part)+1, -1);
        $newDir = generate_new_download_directory($dir, $defaultDownloadDir, $userDownloadDir);
        $dSetDirectory[0][0] = $d_set_directory_part.'"'.$newDir.'"';
    } else {
        $req->params->addChild('param')->addChild('value')->addChild('string')[0] = 'd.set_directory="'.$userDownloadDir.'"';;
    }
}

function replace_download_dir_and_set_username_on_load_multicommand(SimpleXMLElement $req, $username, $defaultDownloadDir, $userDownloadDir) {
    $executeCommands = [];          
    $loadCommand = null;

    // check if load* commands are executed 
    foreach ($req->params->param->value->array->data->value as $command) {
        $methodNameValue = $command->xpath('struct/member/name["methodName"]/following-sibling::value/string');
        if ($methodNameValue != null) {
            $v = (string) $methodNameValue[0];
            if (strpos($v, "load") === 0) {
                $loadCommand = $command;
            } else if ($v == "execute") {
                $executeCommands[] = $command;
            }
        }
    }
    if ($loadCommand == null) {
        return ;
    }
    
    global $d_set_directory_part;

    // here we select parameter d.set_directory= , if any, from command load*
    if (($dSetDirectory = $loadCommand->xpath('struct/member/name["params"]/following-sibling::value/array/data/value/string[starts-with(text(), "'.$d_set_directory_part.'")]')) != null) {
        $dir = substr((string) $dSetDirectory[0], strlen($d_set_directory_part)+1, -1);

        $newDir = generate_new_download_directory($dir, $defaultDownloadDir, $userDownloadDir);
        $dSetDirectory[0][0] = $d_set_directory_part.'"'.$newDir.'"';

        foreach ($executeCommands as $executeCommand) {
            $execCommandDirs = $executeCommand->xpath('struct/member/name["params"]/following-sibling::value/array/data/value/string[text() = "'.$dir.'"]');
            if ($execCommandDirs != null) {
                foreach ($execCommandDirs as $execCommandDir) {
                    $execCommandDir[0] = $newDir;
                }
            }
        }
    } else {
        $d = $loadCommand->xpath('struct/member/name["params"]/following-sibling::value/array/data');
        if ($d != null) {
            $dd = $d[0]->addChild('value')->addChild('string');
            $dd[0] = 'd.set_directory="'.$userDownloadDir.'"';
        }
    }
    
    // add custom variable with username
    if (($dData = $loadCommand->xpath('struct/member/name["params"]/following-sibling::value/array/data')) != null) {
        append_username_to_params($dData[0], $username);
    }
}



///////////////////////////////////////////////
// d.multicall command

function append_username_request_to_get_all_command(SimpleXMLElement $req) {
    $req->params->addChild("param")->addChild("value")->addChild("string")[0] = "d.get_custom=x-username";
}

function filter_by_username_get_all_command(SimpleXMLElement $req, $username) {    
    $itemsToDelete = [];
    foreach ($req->params->param->value->array->data->value as $v) {
        $vv = $v->xpath('array/data/value[last()]');
        if ($vv == null || $vv[0]->string != $username) {
            $itemsToDelete [] = $v;
        }
    }
    foreach ($itemsToDelete as $i) {
        $dom = dom_import_simplexml($i);
        $dom->parentNode->removeChild($dom);
    }
}