<?php
echo "<!DOCTYPE HTML>";
include "utils.php";
include "mPDO.php";
if(file_exists("loaded.temp")) unlink("loaded.temp");

function logging($message, $level){
    $myfile = fopen("browser.log", "a");
    $txt = $level.":root:".$message."\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}
$start = microtime(true);
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
logging("Function 'read_database' lasted ".(microtime(true)-$start)." seconds", "INFO");
$query = array();
foreach(explode("&", $_SERVER['QUERY_STRING']) as $i){
    $j = explode("=", $i);
    $query[$j[0]] = (int) $j[1];
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

$num = init()["PAGE_IMAGES"];
?>
<html lang="de">
<head>
  <title style="font-family: 'AntonZora';">SimPic</title>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0," name="viewport">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
  <link rel="stylesheet" href="../static/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <!-- Add additional CSS in static file -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="../static/css/styles_browser.css">
  <link rel="stylesheet" href="../static/css/w3.css">
  <link rel="shortcut icon" href="../static/images/favicon.ico" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script type="text/javascript" src="../static/javascript/ltrim.js"></script>
    <script type="text/javascript" src="../static/javascript/sort_table.js"></script>
    
    <script src="jquery.js"></script>
    <script src="../static/javascript/jquery.js"></script>
    <script type="text/javascript" src="../static/javascript/search.js"></script>
<script src="packery.pkgd.js">

    
    <script type="text/javascript" >
    window.onload = function() {
        SortTable.init();
    }
    check_none();</script>
<h1 style="background-color: chartreuse;text-align: center;">Verzeichnis: 
    <?php
    if(!array_key_exists("id", $query)){
        $query["id"] = 1;
    }if(!array_key_exists($query["id"], $contents)){
        $query["id"] = 1;
    }if(!array_key_exists("folder", $query)){
        $query["folder"] = 1;
    }if(!array_key_exists("file", $query)){
        $query["file"] = 1;
    }
    echo $contents[$query["id"]]; 
    ?></h1>
</head>
<body class=".school_body">
    <div id="whole_page">
    <style>
        body {
          background-color: green;
        }
        .pointer-link {
            cursor: pointer;
        }
        
        </style>
    <?php
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("/Images", "", $dir);
    $dir = str_replace("\\", "/", $dir);
    $directories = glob($dir."/static/".$contents[$query["id"]]. '/*' , GLOB_ONLYDIR);
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
        return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG,webm,mp4}", GLOB_BRACE);
    }

    function calculate_width($path){
        try{
            list($width, $height) = getimagesize($path);
            $rel = $height / $width;
            if($height <= $width){
                return 300;
            }else{
                return (int) (300 / $rel);
            }
        }catch(DivisionByZeroError $e){
            return 300;
        }
    }
    
    function update_database($dir){
        $info = init();
        $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
        $sql = "SELECT id, path FROM objects";
        $yet = array();
        $mdir = str_replace("\\Images", "", getcwd());
        $mdir = str_replace("/Images", "", $mdir);
        $mdir = str_replace("\\", "/", $mdir)."/static/";
        foreach ($pdo->query($sql) as $row) {
            $yet[] = $row['path'];
        }
        $query = "INSERT INTO objects (path) VALUES "; //Prequery
        $files = array();
        foreach(listdir($dir) as $element){
            if(in_array(str_replace($mdir, "", $element), $yet) && count($files) < $info["DATABASE_UPDATE_FILES"]) continue;
            else $files[] = [str_replace($mdir, "", $element)];
        }
        if(count($files) == 0) return;
        $db = new mPDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
        $db->beginTransaction();
        $stmt = $db->multiPrepare('INSERT INTO objects (path)', $files);
        $stmt->multiExecute($files);
        $db->commit();
        logging(count($files)." elements added to database", "INFO");
    }

    function cronicles($contents, $query){
        $file = fopen("chronicles.data", "a");
        # Format: Adress-+-Time
        $str = "Image-Browser-+-".$contents[$query["id"]]."-+-".date("d. F Y,  H:i:s")."\n";
        fwrite($file, $str);
        fclose($file);
    }
    cronicles($contents, $query);
    
    function make_thumbnail ($source, $dest, $nw = 150, $nh = 150){
        $size = @getimagesize($source);
        $w = $size[0];
        $h = $size[1];
        $stype = explode(".", $source);
        $stype = $stype[count($stype)-1];
        switch($stype) {
            case 'gif':
                $simg = imagecreatefromgif($source);
                break;
            case 'jpg':
                $simg = imagecreatefromjpeg($source);
                break;
            case 'png':
                $simg = imagecreatefrompng($source);
                break;
        }
        $dimg = imagecreatetruecolor($nw, $nh);
        $wm = $w/$nw;
        $hm = $h/$nh;
        $h_height = $nh/2;
        $w_height = $nw/2;
        if($w> $h) {
            $adjusted_width = $w / $hm;
            $half_width = $adjusted_width / 2;
            $int_width = $half_width - $w_height;
            imagecopyresampled($simg,$dimg,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);
        } elseif(($w < $h) || ($w == $h)) {
            $adjusted_height = @($h / $wm);
            $half_height = $adjusted_height / 2;
            $int_height = $half_height - $h_height;
            imagecopyresampled($dimg,$simg,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
        } else {
            imagecopyresampled($dimg,$simg,0,0,0,0,$nw,$nh,$w,$h);
        }
                            
        imagejpeg($dimg,$dest,100);
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
    $current = $dir."/static/".$contents[$query["id"]];
    
    $start = microtime(true);
    update_database($current);
    logging("Function 'update_database' lasted ".(microtime(true)-$start)." seconds", "INFO");
    ?>
    <div class="container-fluid">
    <div class="row">
    <div class="col-sm-2" id="sidebar">
        <script>
            var elem = document.querySelector('body');
            //move_on_scroll();
            move_scroll();
        </script>
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
            # $upon = "/".$upon;
            # echo $upon;
            if(in_array(str_replace($dir."/static/", "", $upon), $contents)){
                $lnk = array_search(str_replace($dir."/static/", "", $upon), $contents);
                $name = explode("/", $contents[$query["id"]])[count(explode("/", $contents[$query["id"]]))-1];
                $upon = explode("/", $contents[$query["id"]]);
                $up = "";
                for($i = 0; $i < count($upon)-1; $i++){
                    $up .= $upon[$i]."/";
                }
                $var = array_search($name, listonlydir($dir."/static/".$up));
                if($var === NULL){
                    $var = 1;
                }else{
                    $var = (int)($var / $num) + 1;
                }
                echo "<a href='index.php?id=".$lnk."&folder=".$var."'>Oben</a>";
            }else{
                echo "<a href='../'>Oben</a>";
            }
            ?>
        </ul>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: aqua;text-align: center;">
            <?php
            echo "Bilder: <p id='num_img'>".count(listdir($dir."/static/".$contents[$query["id"]]))."</p>";
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
            text = '';
            //elem.addEventListener("keydown", TasteGedrückt );
            </script>
            <input class="form-control" placeholder="Suche.." type="text" onkeypress="results(event, this.value)" id="search" />
            <!--<input placeholder="Suche.." type="text" onkeyup="showHint(this.value)" onkeypress="results(event, this.value)" id="search" />
            <p><span id="txtHint"></span></p>-->
            <?php
            $ok = str_replace("'", "%27", $dir."/static/".$contents[$query['id']]);
            ?>
            <a class="pointer-link" onclick="<?php echo "actions('new_dir', '".$ok."')"; ?>">
                <img src='../static/images/Image.png' width=75px style='text-align: center' title="Neuen Ordner erstellen"/>
            </a>
            <a class="pointer-link" type="file" onclick="<?php echo "actions('upload', '".$ok."')"; ?>">
                <img src='../static/images/upload.png' width=75px style='text-align: center' title="Dateien hinzufügen"/>
            </a>
            <!--<a class="pointer-link" type="file" onclick="<?php echo "actions('change', '".$ok."')"; ?>">
                <img src='../static/images/change.png' width=75px style='text-align: center' title="Dateien tauschen"/>
            </a>-->
            <br>
            <br>
            <a class="pointer-link" onclick="check_all()">
                <p style="font-size:large vw">Alles auswählen</p>
            </a>
            <a class="pointer-link" onclick="check_none()">
                <p style="font-size:large vw">Nichts auswählen</p>
            </a>
            <p id="selection_counter">0 ausgewählt</p>
            <!--<p>Vorschläge: 
            <div style="height:200px;overflow:scroll;padding:5px;background-color:#FCFADD;
                color:#714D03;border:4px double #DEBB07;">
            <span id="txtHint"></span>
            </div></p>-->
        </ul>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: lightblue;text-align: center;">
            <a href="chronicles.php">Chronik</a>
        </ul>
    </div>
    <div class="col-sm-10" style="text-align: center;">
        <p style="text-align: center; background-color: aquamarine;">Ordner</p>
        <?php
            $table = array();
            $row = array();
            for($i = 0; $i < count($directories);$i++){
                if($query["folder"] * $num > $i && $i >= ($query["folder"] - 1) * $num) {
                    $give = str_replace($dir."/static/", "", $directories[$i]);
                    if(!in_array($give, $contents)){
                        $info = init();
                        $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
                        $statement = $pdo->prepare("INSERT INTO content (path, visits) VALUES (?, ?)");
                        try{
                            $statement->execute(array($give, 0));  
                        }catch(Exception $e){
                            echo $e;
                        } 
                        $contents = read_database();
                    }
                    array_push($row, $directories[$i]);
                    if(($i+1) % 3 == 0){
                        array_push($table, $row);
                        $row = array();
                    }
                }
                
            }
            array_push($table, $row);
            if (!count($table[0]) == 0){
                ?>
                <!--<div class="pagination" style="background-color: rgba(255, 208, 0, 0.75); text-align: center; width: 100%; ">
                    <span style="text-align: center;" class="step-links">
                            <a href="<?php echo "?id=".$query["id"]."&folder=1";?>">&laquo; Anfang</a>
                            <a href="<?php if(count($directories) <= $num){
                                echo "?id=".$query["id"]."&folder=1";
                            }elseif($query["folder"] > 1){
                                echo "?id=".$query["id"]."&folder=".(int)($query["folder"]-1);
                            }else{
                                echo "?id=".$query["id"]."&folder=1";
                            }?>">Vorherige</a>
                        <span class="current"><?php 
                        echo "Seite ".$query["folder"]." von ".(int)((count($directories) / $num)+1) ?>
                        </span>
                            <a href="<?php if(count($directories) <= $num){
                                echo "?id=".$query["id"]."&folder=1";
                            }elseif($query["folder"] < (int)((count($directories) / $num)+1)){
                                echo "?id=".$query["id"]."&folder=".(int)($query["folder"]+1);
                            }else{
                                echo "?id=".$query["id"]."&folder=".(int)((count($directories) / $num)+1);
                            }?>">Nächste</a>
                            <a href="<?php if(count($directories) <= $num){
                                echo "?id=".$query["id"]."&folder=1";
                            }else{
                                echo "?id=".$query["id"]."&folder=".(int)((count($directories) / $num)+1);
                            }?>">Ende &raquo;</a>
                    </span>
                </div>-->
                <div style="width:100%; height: 20px;" id="container_folder" class="container_folder">
                </div>
                <!--<div class="pagination" style="background-color: rgba(255, 208, 0, 0.75); text-align: center; width: 100%; ">
                    <span style="text-align: center;" class="step-links">
                            <a href="<?php echo "?id=".$query["id"]."&folder=1";?>">&laquo; Anfang</a>
                            <a href="<?php if(count($directories) <= $num){
                                echo "?id=".$query["id"]."&folder=1";
                            }elseif($query["folder"] > 1){
                                echo "?id=".$query["id"]."&folder=".(int)($query["folder"]-1);
                            }else{
                                echo "?id=".$query["id"]."&folder=1";
                            }?>">Vorherige</a>
                        <span class="current"><?php 
                        echo "Seite ".$query["folder"]." von ".(int)((count($directories) / $num)+1) ?>
                        </span>
                            <a href="<?php if(count($directories) <= $num){
                                echo "?id=".$query["id"]."&folder=1";
                            }elseif($query["folder"] < (int)(count($directories) / $num)+1){
                                echo "?id=".$query["id"]."&folder=".(int)($query["folder"]+1);
                            }else{
                                echo "?id=".$query["id"]."&folder=".(int)((count($directories) / $num)+1);
                            }?>">Nächste</a>
                            <a href="<?php if(count($directories) <= $num){
                                echo "?id=".$query["id"]."&folder=1";
                            }else{
                                echo "?id=".$query["id"]."&folder=".(int)((count($directories) / $num)+1);
                            }?>">Ende &raquo;</a>
                    </span>
                </div>-->
                <?php
            }else{
                echo "<p style='text-align: center'>Keine Ordner vorhanden</p>";
            }
        ?>
        <p style="text-align: center; background-color: aqua;">Dateien</p>
        <?php
            $table = array();
            $row = array();
            $fls = listdir($dir."/static/".$contents[$query["id"]]);
            for($i = ($query["file"] - 1) * $num; $query["file"] - 1 <= $i && $i < $query["file"] * $num && $i < count($fls);$i++){
            // for($i = 0; $i < count($fls);$i++){
                if($query["file"] * $num > $i && $i >= ($query["file"] - 1) * $num) {
                    // echo $i."<br>";
                    $give = str_replace($dir."/static/", "", $fls[$i]);
                    array_push($row, $fls[$i]);
                    if(($i+1) % 3 == 0){
                        array_push($table, $row);
                        $row = array();
                    };
                }
            }
            array_push($table, $row);
            if (!count($table[0]) == 0){
                ?>           
                <!--<div class="pagination" style="background-color: rgba(255, 208, 0, 0.75); text-align: center; width: 100%; ">
                    <span style="text-align: center;" class="step-links">
                            <a href="<?php echo "?id=".$query["id"]."&file=1";?>">&laquo; Anfang</a>
                            <a href="<?php if(count($fls) <= $num){
                                echo "?id=".$query["id"]."&file=1";
                            }elseif($query["file"] > 1){
                                echo "?id=".$query["id"]."&file=".(int)($query["file"]-1);
                            }else{
                                echo "?id=".$query["id"]."&file=1";
                            }?>">Vorherige</a>
                        <span class="current"><?php 
                        echo "Seite ".$query["file"]." von ".(int)((count($fls) / $num)+1) ?>
                        </span>
                            <a href="<?php if(count($fls) <= $num){
                                echo "?id=".$query["id"]."&file=1";
                            }elseif (count($fls)%$num == 0){
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num));
                            }elseif($query["file"] < (int)((count($fls) / $num)+1)){
                                echo "?id=".$query["id"]."&file=".(int)($query["file"]+1);
                            }else{
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num)+1);
                            }?>">Nächste</a>
                            <a href="<?php if(count($fls) <= $num){
                                echo "?id=".$query["id"]."&file=1";
                            }elseif (count($fls)%$num == 0){
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num));
                            }else{
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num)+1);
                            }?>">Ende &raquo;</a>
                    </span>
                </div>-->
                <div style="width:100%; height: 20px;" id="container" class="container">
                </div>
                <!--<div class="pagination" style="background-color: rgba(255, 208, 0, 0.75); text-align: center; width: 100%; ">
                    <span style="text-align: center;" class="step-links">
                            <a href="<?php echo "?id=".$query["id"]."&file=1";?>">&laquo; Anfang</a>
                            <a href="<?php if(count($fls) <= $num){
                                echo "?id=".$query["id"]."&file=1";
                            }elseif($query["file"] > 1){
                                echo "?id=".$query["id"]."&file=".(int)($query["file"]-1);
                            }elseif (count($fls)%$num == 0){
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num));
                            }else{
                                echo "?id=".$query["id"]."&file=1";
                            }?>">Vorherige</a>
                        <span class="current"><?php 
                        echo "Seite ".$query["file"]." von ".(int)((count($fls) / $num)+1) ?>
                        </span>
                            <a href="<?php if(count($fls) <= $num){
                                echo "?id=".$query["id"]."&file=1";
                            }elseif($query["file"] < (int)((count($fls) / $num)+1)){
                                echo "?id=".$query["id"]."&file=".(int)($query["file"]+1);
                            }else{
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num)+1);
                            }?>">Nächste</a>
                            <a href="<?php if(count($fls) <= $num){
                                echo "?id=".$query["id"]."&file=1";
                            }elseif (count($fls)%$num == 0){
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num));
                            }else{
                                echo "?id=".$query["id"]."&file=".(int)((count($fls) / $num)+1);
                            }?>">Ende &raquo;</a>
                    </span>
                </div>-->
                <?php
            }else{
                echo "<p style='text-align: center'>Keine Bilder vorhanden</p>";
            }
        $current = $dir."/static/".$contents[$query["id"]];
        update_database($current);
        ?>
</div>

    </div>
    </div>
    </div>
    <script type="text/javascript">
        check_none();
        var loaded_img = 0;
        var loaded_fol = 0;
        
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
        
        function scroll(repeat){
            if(get_all() > loaded_img) {
                getresult('dynamic_image.php?elements='+loaded_img+'&id='+parseInt(<?php echo $query["id"];?>), repeat);
            }if(<?php echo count($directories); ?> > loaded_fol) {
                getresult('dynamic_folder.php?elements='+loaded_fol+'&id='+parseInt(<?php echo $query["id"];?>), repeat, "container_folder");
            }create_grid();
        };

        window.onscroll = function (e) {
            let myDiv = document.getElementById('whole_page');
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight)  {
                for(let i=0; i< 2;i++) {
                    scroll(1);
                    loaded_img++;
                    loaded_fol++;
                }
            }
        }

        window.addEventListener("load", function (e) {
            let myDiv = document.getElementById('whole_page');
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight)  {
                for(let i=0; i< 20;i++) {
                    scroll(1);
                    loaded_img++;
                    loaded_fol++;
                }
            }
        });
        setTimeout(create_grid, 3000);
        // document.addEventListener('DOMContentLoaded', setTimeout(create_grid, 500), false);
    </script>
    </div>
</body>
</html>