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


function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}


function fetch_results_index($name, $id){
    $name = clear($name);
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $names = explode(";", $name);
    $sql = "SELECT id, path FROM objects WHERE (";
    foreach($names as $name){
        $sql .= "LOWER(path) LIKE LOWER('%$name%') AND ";
    }
    $sql = str_lreplace("AND ", "", $sql);
    $sql .= ")";
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("\\", "/", $dir);
    $c = 0;
    foreach ($pdo->query($sql) as $row) {
        if(file_exists($dir."/static/".$row['path']) && is_file($dir."/static/".$row['path'])){
            if($c == $id){
                return $row['path'];
            }
            $c++;
        }
    }

}


function fetch_results($name){
    $name = clear($name);
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $names = explode(";", $name);
    $contents = array();
    $sql = "SELECT id, path FROM objects WHERE (";
    foreach($names as $name){
        $sql .= "LOWER(path) LIKE LOWER('%$name%') AND ";
    }
    $sql = str_lreplace("AND ", "", $sql);
    $sql .= ")";
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("\\", "/", $dir);
    foreach ($pdo->query($sql) as $row) {
        if(file_exists($dir."/static/".$row['path']) && !in_array($row['path'], $contents)){
            $contents[$row["id"]] = $row['path'];
        }
    }
    return $contents;
}
?>