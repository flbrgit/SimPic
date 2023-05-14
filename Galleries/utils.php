<?php
function init(){
    $resources = file("../browser.ini");
    $config = array();
    foreach($resources as $line){
        if(strpos($line, "#") === 0) continue;
        $line = str_replace("\n", "", $line);
        $line = str_replace("\r", "", $line);
        $new = explode(": ", $line, 2);
        error_reporting(E_ERROR | E_PARSE);
        try{
            $config[$new[0]] = $new[1];
        }catch(Exception $e){
            $config[$new[0]] = '';
        }
    }
    $config["PAGE_IMAGES"] = (int) $config["PAGE_IMAGES"];
    return $config;
}
?>