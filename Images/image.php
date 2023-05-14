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
$contents = read_database();
$query = array();
foreach(explode("&", $_SERVER['QUERY_STRING']) as $i){
    $j = explode("=", $i);
    $query[$j[0]] = (int) $j[1];
}
$q = $_SERVER['QUERY_STRING'];
$q = explode("=", $q);
$d = false;
foreach($q as $key){
    if($d === true){
        /**$key = str_replace("%C3%A4", "ä", $key);
        $key = str_replace("%C3%B6", "ö", $key);
        $key = str_replace("%C3%BC", "ü", $key);
        $key = str_replace("%C3%84", "Ä", $key);
        $key = str_replace("%C3%96", "Ö", $key);
        $key = str_replace("%C3%9C", "Ü", $key);
        $key = str_replace("%C3%9F", "ß", $key);*/
        $key = urldecode($key);
        $query["name"] = $key;
    }
    if(strpos($key, "name") !== false){
        $d = true;
    }
}
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
function read_tags($name){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, tags FROM objects WHERE path LIKE '%$name'";
    $row = $pdo->query($sql)->fetch();
    return explode(";", $row["tags"]);
}
$gallery = read_gallery();
?>
<html lang="de">
<head>
  <title style="font-family: 'AntonZora';">SimPic</title>
  <meta charset="utf-8">
  <meta content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" name="viewport">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
  <link rel="stylesheet" href="../static/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <!-- Add additional CSS in static file -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="../static/css/styles_browser.css">
  <link rel="shortcut icon" href="../static/images/favicon.ico" />
  <script type="text/javascript" src="../static/javascript/ltrim.js"></script>
    <script type="text/javascript" src="../static/javascript/sort_table.js"></script>
    <script type="text/javascript" src="../static/javascript/search.js"></script>
    <script type="text/javascript" >
    window.onload = function() {
        SortTable.init();
    }
    </script>
    
<h1 style="background-color: chartreuse;text-align: center;">Bild: 
    <?php 
    if(!array_key_exists($query["id"], $contents)){
        $query["id"] = 1;
    }if(!array_key_exists("folder", $query)){
        $query["folder"] = 1;
    }if(!array_key_exists("file", $query)){
        $query["file"] = 1;
    }
    echo $query["name"]; 
    ?></h1>
</head>
<body class=".school_body">
    <style>
        body {
          background-color: green;
        }
        </style>
    <?php
    $dir = str_replace("\\Images", "", getcwd());
    # $dir = str_replace("/Images", "", $dir);
    $dir = str_replace("\\", "/", $dir);
    $current = $dir."/static/".$contents[$query["id"]];
    function calculate_width($path){
        list($width, $height) = getimagesize($path);
        $rel = $height / $width;
        if($height <= $width){
            return 800;
        }else{
            return (int) (800 / $rel);
        }
    }
    function listdir($path){
        /**$ds = glob($path. '/*' , GLOB_ONLYDIR);
        $files = scandir($path);
        $elem = array();
        foreach($files as $file) {
            if($file == '.' || $file == '..' || in_array($path."/".$file, $ds)) continue;
            array_push($elem, $file);
        }
        return $elem;*/
        
        $elem = array();
        $files = scandir($path);
        return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG}", GLOB_BRACE);
    }
    function cronicles($contents, $query){
        $file = fopen("chronicles.data", "a");
        # Format: Adress-+-Time
        $str = "Image-View-+-".$contents[$query["id"]]."/".$query["name"]."-+-".date("d. F Y,  H:i:s")."\n";
        fwrite($file, $str);
        fclose($file);
    }
    cronicles($contents, $query);
    
    function update_database($dir){
        $info = init();
        $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
        $sql = "SELECT id, path FROM objects";
        $yet = array();
        $mdir = str_replace("\\Images", "", getcwd());
        $mdir = str_replace("\\", "/", $mdir)."/static/";
        foreach ($pdo->query($sql) as $row) {
            $yet[] = $row['path'];
        }
        foreach(listdir($dir) as $element){
            if(in_array(str_replace($mdir, "", $dir)."/".$element, $yet)){
                continue;
            }
            $statement = $pdo->prepare("INSERT INTO objects (path) VALUES (:pth)");
            $statement->execute(array('pth' => str_replace($mdir, "", $dir)."/".$element));
        }
    }
    function visited($contents, $query){
        $info = init();
        $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
        $stmt = "SELECT * FROM `objects` WHERE PATH = '".$contents[$query["id"]]."/".$query["name"]."' LIMIT 1";
        $row = $pdo->query($stmt)->fetch();
        $visits = $row["VISITS"] + 1;
        $statement = $pdo->prepare("UPDATE `objects` SET VISITS = ? WHERE PATH = ?");
        $statement->execute(array($visits, $contents[$query["id"]]."/".$query["name"]));
        return $visits;
    }
    $current = $dir."/static/".$contents[$query["id"]];
    update_database($current);
    $visits = visited($contents, $query);
    ?>
    <div class="container-fluid">
    <div class="row">
    <div class="col-sm-2">
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: chocolate;text-align: center;">
            <a href="../">Home</a>
            <br >
            <?php
            $upon = "";
            $a = explode("/", $current);
            for($i = 0; $i < count($a)-1; $i++){
                if(count(str_split($upon)) == 1){
                    $upon = $a[$i];
                }else{
                    $upon = $upon."/".$a[$i];
                }            
            }
            $upon = $upon;  # "/".$upon;
            if(in_array(str_replace($dir."/static/", "", $upon), $contents)){
                $lnk = array_search(str_replace($dir."/static/", "", $upon), $contents);
                $var = array_search($query["name"], listdir($dir."/static/".$contents[$query["id"]]));
                if($var === NULL){
                    $var = 1;
                }else{
                    $var = (int)($var / 30) + 1;
                }
                echo "<a href='index.php?id=".$query["id"]."&file=".$var."'>Oben</a>";
            }else{
                echo "<a href='../'>Oben</a>";
            }
            ?>
        </ul>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: lightblue;text-align: center;">
            <a href="chronicles.php">Chronik</a>
        </ul>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: aqua;text-align: center;">
            <?php
            echo "Bilder: ".count(listdir($dir."/static/".$contents[$query["id"]]))."<br><br>";
            echo "Suche";
            ?>
            <script>
            function openCity(evt, cityName) {
                var i, x, tablinks;
                x = document.getElementsByClassName("city");
                for (i = 0; i < x.length; i++) {
                    x[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("tablink");
                for (i = 0; i < x.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" w3-red", "");
                }
                document.getElementById(cityName).style.display = "block";
                evt.currentTarget.className += " w3-red";
            }
            function move() {
                var elem = document.getElementById("myBar");   
                var width = 1;
                var id = setInterval(frame, 10);
                function frame() {
                    if (width >= 100) {
                    clearInterval(id);
                    } else {
                    width++; 
                    elem.style.width = width + '%'; 
                    elem.innerHTML = width * 1  + '%';
                    }
                }
            }
            var elem = document.querySelector('body'),
            text = '';
            //elem.addEventListener("keydown", TasteGedrückt );
            </script>
            <input placeholder="Suche.." class="form-control" type="text" onkeypress="results(event, this.value)" id="search" />
            <!--<p>Vorschläge: 
            <div style="height:200px;overflow:scroll;padding:5px;background-color:#FCFADD;
                color:#714D03;border:4px double #DEBB07;">
            <span id="txtHint"></span>
            </div></p>-->
            <?php
                $element = $dir."/static/".$contents[$query["id"]]."/".$query["name"];
                if(is_dir($element)){
                    if(count(listdir($element)) == 0 && count(listonlydir($element)) == 0){
                ?>
                <a onclick="<?php echo "actions('delete', '".$element."')"; ?>">
                    <img src='../static/images/delete.png' height=30px style='text-align: center' 
                        title="Ordner löschen"/>
                </a>
                <?php 
                }} 
                ?>
                <a onclick="<?php echo "actions('rename_file', '".$contents[$query['id']].", ".$element."')"; ?>">
                    <img src='../static/images/rename.png' height=30px style='text-align: center' 
                        title="Datei umbenennen"/>
                </a>
                <?php
                $element = urldecode($element);
                if(!in_array(str_replace($dir."/static/", "", $element), $gallery)){                                
                ?>
                <a onclick="<?php echo "actions('add_fav', '".str_replace("'", "%27", $element)."')"; ?>">
                    <img src='../static/images/add_favourite.png' height=30px style='text-align: center' 
                        title="Zu Favoriten hinzufügen"/>
                </a>
                <?php
                }else{
                    ?>
                    <a onclick="<?php echo "actions('sub_fav', '".str_replace("'", "%27", $element)."')"; ?>">
                    <img src='../static/images/dismiss_favourite.png' height=30px style='text-align: center' 
                        title="Aus Favoriten entfernen"/>
                    </a>
                    <?php
                }
                ?>
                <br/></a>
        </ul>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: lightgreen;text-align: center;">
            <p><strong>Name: </strong><?php echo $query["name"]; ?></p>
            <p><strong>Größe: </strong><?php echo round(filesize($dir."/static/".$contents[$query["id"]]."/".$query["name"]) / 1024, 2)." kb"; ?></p>
            <?php
            $infos = getimagesize ($dir."/static/".$contents[$query["id"]]."/".$query["name"]);
            # $gdimage = imagecreatefromjpeg($dir."/static/".$contents[$query["id"]]."/".$query["name"]);
            echo "<p><strong>Abmessungen: </strong>".$infos[0]."x".$infos[1]."</p>";
            # print_r(imageresolution($gdimage));
            echo "<p><strong>MIME-Typ: </strong>".$infos["mime"]."</p>";
            echo "<p><strong>Bits pro Farbe: </strong>".$infos["bits"]."</p>";
            if($infos["mime"] != "image/webp") echo "<p><strong>Kanäle: </strong>".$infos["channels"]."</p>";
            echo "<p><strong>Aufrufe: </strong>".$visits."</p>";
            echo "<p><strong>Letzte Änderung: </strong><br>".date("d. F Y,  H:i:s", filemtime($dir."/static/".$contents[$query["id"]]."/".$query["name"]))."</p>";
            echo "<p><strong>Letzter Zugriff: </strong><br>".date("d. F Y,  H:i:s", fileatime($dir."/static/".$contents[$query["id"]]."/".$query["name"]))."</p>";
            ?>         
        </ul>
        <!--<ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: chartreuse;text-align: center;">
            <?php
            # $tags = read_tags($query["name"]);
            # print_r($tags);
            ?>
            <br>
        </ul>-->
    </div>
    <div class="col-sm-10">
            <div style="background-color: aqua;text-align: center;">
        <?php 
        $elements = listdir("../static/".$contents[$query['id']]); 
        $before = NULL;
        $after = NULL;
        $found = false;
        foreach($elements as $element){
            if($found){
                $after = basename($element);
                $found = false;
            }
            if(basename($element) == $query["name"]){
                $found = true;
                continue;
            }
            if(!$found && $after === NULL){
                $before = basename($element);
            }
        }
        if($before !== NULL){
            echo "<a href=?id=".$query["id"]."&file=".$query["file"]."&name=".$before.">".
            "<img src='../static/images/arrow_left.png' class='img-responsive' width=50px style='position: absolute; 
            left: 2%; top: 7%'/></a>";
        }
        if($after !== NULL){
            echo "<a href=?id=".$query["id"]."&file=".$query["file"]."&name=".$after.">".
            "<img src='../static/images/arrow_right.png' class='img-responsive' width=50px style='position: absolute; 
            right: 2%; top: 7%'/></a>";
        }
        ?>
            <script>
                var elem = document.querySelector('body'),
                text = '';
                elem.addEventListener("keydown", TasteGedrückt );
                function TasteGedrückt (evt) {
                let zeichen = String.fromCharCode(evt.charCode);
                if (evt.keyCode == 37 && "<?php 
                if($before !== NULL){
                    echo "?id=".$query["id"]."&file=".$query["file"]."&name=".$before;}?>"){
                window.location = "<?php 
                if($before !== NULL){
                    echo "?id=".$query["id"]."&file=".$query["file"]."&name=".$before;}?>";
                }
                else if (evt.keyCode == 39 && "<?php 
                if($after !== NULL){
                    echo "?id=".$query["id"]."&file=".$query["file"]."&name=".$after;}?>"){
                window.location = "<?php 
                if($after !== NULL){
                    echo "?id=".$query["id"]."&file=".$query["file"]."&name=".$after;}?>";
                }
                }
            </script>
            <p>Pfad: <?php echo $contents[$query['id']]."/".$query["name"];?></p>
            </div>            
            <div style="text-align: center;"><?php 
            $ko = str_replace("'", "%27", $contents[$query['id']]);
            $path = "../static/".$ko."/".$query["name"];
            $width = calculate_width($dir."/static/".$contents[$query["id"]]."/".$query["name"]);
            echo "<img id='myimage' src='".$path."' width=".$width."px class='img-responsive img-thumbnail' 
            style='text-align: center;' class='center'/>";
            ?>
    </div>
</body>
</html>
