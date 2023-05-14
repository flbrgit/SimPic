<?php
include "utils.php";
function read_database(){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path FROM content";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}
function read_objects(){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path FROM objects";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}
$contents = read_database();
function read_gallery(){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, folder FROM gallery";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}
function get_folder($folder){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, folder FROM gallery WHERE folder == $folder";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}
function listonlydir($path){
    $elem = array();
    $files = scandir($path);
    foreach($files as $file) {
        if($file == '.' || $file == '..' || !is_dir($path."/".$file)) continue;
        array_push($elem, $file);
    }
    return $elem;
}
$gallery = read_gallery();
$objects = read_objects();
function get_possible_folders($folder){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path FROM content";
    $contents = array();
    $pu = explode("/", $folder);
    $u = "";
    for($i = 0; $i < count($pu) - 1; $i++){
        if($i == 0) $u .= $pu[$i];
        else $u .= "/".$pu[$i];
    }
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("\\", "/", $dir);
    $contents[] = "Browser";
    if($u !== "") $contents[] = $u;
    foreach ($pdo->query($sql) as $row) {
        if (file_exists($dir."/static/".$row['path']) && strpos($row["path"], $folder) !== false && 
            !array_search($row["path"], $contents)){
            if($row["path"] != $folder && 
                count(explode("/", $row["path"])) - 1 == count(explode("/", $folder))) $contents[] = $row["path"];
            }
    }
    for($i = 0; $i < 1; $i++){
        foreach($contents as $element){
            $paths = listonlydir($dir."/static/".$element);
            foreach($paths as $path){
                # if(!array_search($element."/".$path, $contents) && $element."/".$path != $folder) 
                $contents[] = $element."/".$path;
            }
        }
    }
    
    return $contents;
}

function prepare_directories($path){
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("\\", "/", $dir);
    $path = $dir."/static/".$path;
    $paths = scandir($path);
    foreach($paths as $p){
        if(is_file($path."/".$p) || $p == ".." || $p == ".") continue;
        $d = str_replace($dir."/static/", "", $path."/".$p);
        echo "<details>";
        echo "<summary>".$p."</summary>";
        echo "<a class='pointer-link' name='fold'>".$d."</a>";
        echo "</details>";
    }
}
?>
<html lang="de">
<head>
  <title style="font-family: 'AntonZora';">Browser.de</title>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0," name="viewport">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <!-- Add additional CSS in static file -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="../static/css/styles_browser.css">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="shortcut icon" href="../static/images/favicon.ico" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script type="text/javascript" src="../static/javascript/ltrim.js"></script>
    <script type="text/javascript" src="../static/javascript/sort_table.js"></script>
    <script type="text/javascript" src="search.js"></script>
    <script type="text/javascript" >
    window.onload = function() {
        SortTable.init();
    }
    check_none();
    </script>
<h1 style="background-color: chartreuse;text-align: center;">Chronik</h1>
</head>
<body class=".school_body">
    <style>
        body {
          background-color: green;
        }
        .pointer-link {
            cursor: pointer;
        }
        </style>
    <?php
    
    function listdir($path){
        /**$ds = glob($path. '/*' , GLOB_ONLYDIR);
        $files = scandir($path);
        $elem = array();
        $allowed = ["gif", "jpg", "GIF", "JPG", "png", "PNG"];
        foreach($files as $file) {
            if($file == '.' || $file == '..' || in_array($path."/".$file, $ds) || 
                !in_array(explode(".", $file)[count(explode(".", $file))-1], $allowed)) continue;
            array_push($elem, $file);
        }
        return $elem;
        $elem = array();
        $files = scandir($path);**/
        return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG}", GLOB_BRACE);
    }

    function search($tb, $col, $cond){
        try{
            $info = init();
            $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
            $sql = "SELECT $col FROM $tb WHERE $cond";
            $user = $pdo->query($sql)->fetch();
            if(count($user) != 0){
                return $user["id"];
            }else{
                return false;
            }
        }catch(Exception $e){
            return false;
        }
    }
    ?>
    <div class="container-fluid">
    <div class="row">
    <div class="col-sm-2">
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: chocolate;text-align: center;">
            <a href="../">Home</a>
            <br >
        </ul>
    </div>
    <div class="col-sm-10">
        <p style="text-align: center; background-color: aquamarine;">Chronik</p>
        <?php
            function parse_line($line): array{
                $ext = array();
                $line = explode("-+-", $line);
                error_reporting(E_ERROR | E_PARSE);
                $ext["type"] = $line[0];
                $ext["path"] = $line[1];
                $ext["date"] = $line[2];
                return $ext;
            }

            $file = fopen("chronicles.data", "r");
            $lines = array();
            while(! feof($file))  {
                $lines[] = fgets($file);
              }
            fclose($file);
            echo "<table style='width:100%; height: 20px;'>";
            echo "<tr><th>Typ</th>";
            echo "<th>Pfad</th>";
            echo "<th>Datum</th></tr>";
            foreach($lines as $line){
                echo "<tr>";
                $line = parse_line($line);
                echo "<td>".$line["type"]."</td>";
                if($line["type"] == "Image-Browser"){
                    if($line["path"] == "/"){
                        echo "<td><a href='index.php?id=1'>".$line["path"]."</a></td>";
                    }
                    else{
                        $id = array_search($line["path"], $contents);
                        echo "<td><a href='index.php?id=".$id."'>".$line["path"]."</a></td>";
                    }
                }else{
                    $id = array_search(dirname($line["path"]), $contents);
                    echo "<td><a target='_blank' href='image.php?id=".$id."&file="."1"."&name=".basename($line["path"])."'>".$line["path"]."</a></td>";
                }
                echo "<td>".$line["date"]."</td>";
                echo "</tr>";
            }
            echo "</table>";
        ?>
</div>

    </div>
    </div>
</body>
</html>