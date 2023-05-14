<?php
include "utils.php";
function read_database(){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, visited FROM content";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}
function read_db(){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path FROM objects";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}

function find_element($elements, $name){
    $names = explode(";", $name);
    $found = array();
    foreach($names as $n){
        $n = str_replace(";", "", $n);
        foreach($elements as $element){
            if (strpos(strtolower($element), strtolower($n)) !== false && !in_array($element, $found)) {
                $found[] = $element;
            }
        }
    }
    return $found;
}
$contents = read_db();
$pths = read_db();
$query = array();
foreach(explode("&", $_SERVER['QUERY_STRING']) as $i){
    $j = explode("=", $i);
    $query[$j[0]] = $j[1];
}
$results = find_element($contents, $query["tag"]);
$dir = str_replace("\\Images", "", getcwd());
$dir = str_replace("\\", "/", $dir);
$hint = "";
$pth = "";
foreach($results as $result){
    $hint .= ", ".$result;
    $pth .= array_search($result, $contents)."#".str_replace($dir."/static/", "", $result).", ";
}

$hint .= "|||||";
$hint .= $pth;
echo $hint;
?>