<?php
function read_database(){
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
    try{$query[$j[0]] = $j[1];
    }catch(Exception $e){
        //echo $e;
    }
}
function read_gallery(){
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, folder FROM gallery";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        if (stripos($row["path"], ".jpg") !== false){
            $contents[$row["id"]] = $row['path'];
        }
    }
    return $contents;
}
function read_folder($folder){
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, FOLDER FROM gallery WHERE FOLDER = '$folder'";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        if (stripos($row["path"], ".jpg") !== false){
            $contents[$row["id"]] = urldecode($row['path']);
        }
    }
    return $contents;
}
function listonlydir($path){
    $elem = array();
    $files = scandir($path);
    foreach($files as $file) {
        if($file == '.' || $file == '..') continue;
        array_push($elem, $file);
    }
    return $elem;
}
function get_folder($folder){
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, FOLDER FROM gallery";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        if (stripos($row["path"], ".jpg") === false){
            $i = explode("/", $row["path"]);
            $j = explode("/", $folder);
            if(count($j) + 1 != count($i)) continue;
            $k = true;
            for($l = 0; $l < count($i) - 1; $l++){
                if($i[$l] != $j[$l]) $k = false;
            }
            if($k) $contents[$row["id"]] = $row['path'];
        }
    }
    return $contents;
}
function get_possible_folders($folder){
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $sql = "SELECT id, path, FOLDER FROM gallery";
    $contents = array(0 => "NONE");
    foreach ($pdo->query($sql) as $row) {
        if (stripos($row["path"], ".jpg") === false){
            if($row["path"] == "/") continue;
            if($row["path"] != $folder) $contents[] = $row["path"];
        }
    }
    return $contents;
}
?>
<html lang="de">
<head>
  <title style="font-family: 'AntonZora';">SimPic</title>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0," name="viewport">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <!-- Add additional CSS in static file -->
  <link rel="stylesheet" href="../static/css/styles_browser.css">
  <link rel="shortcut icon" href="../static/images/favicon.ico" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script type="text/javascript" src="../static/javascript/ltrim.js"></script>
    <script type="text/javascript" src="../static/javascript/sort_table.js"></script>
    <script src="../static/javascript/jquery.js"></script>
<script src="../static/javascript/packery.pkgd.js">
    <script src="../static/javascript/jquery.js"></script>
    <script type="text/javascript" src="../static/javascript/search.js"></script>
<script src="packery.pkgd.js"></script>
    <script type="text/javascript" >
    window.onload = function() {
        SortTable.init();
    }
    </script>
    <h1 style="background-color: chartreuse;text-align: center;"><u>Gallerie</u>
    <?php
    if(!array_key_exists("id", $query)){
        $query["id"] = 1;
    }if(!array_key_exists($query["id"], $contents)){
        $query["id"] = 1;
    }if(!array_key_exists("folder", $query)){
        $query["folder"] = "NONE";
    }if(!array_key_exists("file", $query)){
        $query["file"] = 1;
    }if(!array_key_exists("pth", $query)){
        $query["pth"] = 1;
    }
    $query["folder"] = urldecode($query["folder"]);
    $content = read_folder($query["folder"]);
    $structure = read_folder($query["folder"]);
    $folder = get_folder($query["folder"]);
    echo "<br>";
    if($query["folder"] == "NONE")echo "Pfad: home";
    else echo "Pfad: ".str_replace("NONE", "home", $query["folder"]);
    ?></h1>
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
            $dir = str_replace("\\Galleries", "", getcwd());
            $dir = str_replace("\\", "/", $dir);
            $directories = glob($dir."/static/".$contents[$query["id"]]. '/*' , GLOB_ONLYDIR);
            function listdir($path){
                $ds = glob($path. '/*' , GLOB_ONLYDIR);
                $files = scandir($path);
                $elem = array();
                $allowed = ["gif", "jpg", "GIF", "JPG", "png", "PNG"];
                foreach($files as $file) {
                    if($file == '.' || $file == '..' || in_array($path."/".$file, $ds) || 
                        !in_array(explode(".", $file)[count(explode(".", $file))-1], $allowed)) continue;
                    array_push($elem, $file);
                }
                return $elem;
            }
            function calculate_width($path){
                try{
                    $path = urldecode($path);
                    list($width, $height) = getimagesize($path);
                    $rel = $height / $width;
                    if($height <= $width){
                        return 300;
                    }else{
                        return (int) (300 / $rel);
                    }
                }catch(DivisionByZeroError $e){
                    return 300;
                }catch(Exception $e){
                    return 300;
                }
            }
            ?>
    <div class="container-fluid">
    <div class="row">
    <div class="col-sm-2" id="sidebar">
        <script>
            //move_on_scroll();
            move_scroll();
        </script>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: chocolate;text-align: center;">
            <a href="../">Home</a>
            <br >
            <?php
            if($query["folder"] != "NONE"){
                $i = explode("/", $query["folder"]);
                $lnk = "";
                for($j = 0; $j < count($i) - 1; $j++){
                    if($j != count($i) - 2){
                        $lnk .= $i[$j]."/";
                    }else{
                        $lnk .= $i[$j];
                    }
                }
                echo "<a href='?folder=".$lnk."'>Oben</a>";
            }else{
                echo "<a href='../'>Oben</a>";
            }
            ?>
        </ul>
        <ul class="sidebar-nav" style="font-family: 'AntonZora';background-color: aqua;text-align: center;">
            <?php
            echo "Bilder: <p id='num_img'>".count($content)."</p><br><br>";
            echo "Suche";
            ?>
        <input class="form-control" placeholder="Suche.." type="text" onkeypress="results(event, this.value)" id="search" />
        <!--<p>Vorschläge: 
        <div style="height:200px;overflow:scroll;padding:5px;background-color:#FCFADD;
            color:#714D03;border:4px double #DEBB07;">
        <span id="txtHint"></span>
        </div></p>-->
        <a onclick="<?php echo "actions('new_dir_gal', '".$query["folder"]."')"; ?>">
            <img src='../static/images/Image.png' width=75px style='text-align: center' title="Neuen Ordner erstellen"/>
        </a>
        <br>
        <br>
        <a class="pointer-link" onclick="check_all()">
            <p style="font-size:large">Alles auswählen</p>
        </a>
        <a class="pointer-link" onclick="check_none()">
            <p style="font-size:large">Nichts auswählen</p>
        </a>
        <p id="selection_counter">0 ausgewählt</p>
        </ul>
    </div>
    <div class="col-sm-10">
            <?php
            $table = array();
            $row = array();
            $fls = $folder;
            $i = 0;
            foreach($fls as $fl){
                if($query["file"] * 30 > $i && $i >= ($query["file"] - 1) * 30) {
                    array_push($row, $fl);
                    if(($i+1) % 3 == 0){
                        array_push($table, $row);
                        $row = array();
                    };
                }
                $i++;
            }
            array_push($table, $row);
            ?>
            
            <p style="text-align: center; background-color: aqua;">Ordner</p>
            <div class="pagination" style="background-color: rgba(255, 208, 0, 0.75); 
                text-align: center; width: 100%; font-size:15px;">
                <span style="text-align: center;" class="step-links">
                        <a href="<?php echo "?folder=".$query["folder"]."&pth=1";?>">&laquo; Anfang</a>
                        <a href="<?php if(count($fls) <= 30){
                            echo "?folder=".$query["folder"]."&pth=1";
                        }elseif($query["pth"] > 1){
                            echo "?folder=".$query["folder"]."&pth=".(int)($query["pth"]-1);
                        }else{
                            echo "?folder=".$query["folder"]."&pth=1";
                        }?>">Vorherige</a>
                    <span class="current"><?php 
                    echo "Seite ".$query["pth"]." von ".(int)((count($fls) / 30)+1) ?>
                    </span>
                        <a href="<?php if(count($fls) <= 30){
                            echo "?folder=".$query["folder"]."&pth=1";
                        }elseif($query["pth"] < (int)((count($fls) / 30)+1)){
                            echo "?folder=".$query["folder"]."&pth=".(int)($query["pth"]+1);
                        }else{
                            echo "?folder=".$query["folder"]."&pth=".(int)((count($fls) / 30)+1);
                        }?>">Nächste</a>
                        <a href="<?php if(count($fls) <= 30){
                            echo "?folder=".$query["folder"]."&pth=1";
                        }else{
                            echo "?folder=".$query["folder"]."&pth=".(int)((count($fls) / 30)+1);
                        }?>">Ende &raquo;</a>
                </span>
            </div>
            <div style="width:100%; height: 20px;" id="container_folder" class="container_folder">
                <?php
                foreach($table as $row){
                    foreach($row as $element){
                        ?>
                        <div class="box">
                        <a onclick="<?php echo "actions('delete_gal', '".$element."')"; ?>">
                            <img src='../static/images/delete.png' height=30px style='text-align: center' 
                                title="Ordner löschen"/>
                        </a>
                        <a onclick="<?php echo "actions('rename_gal', '".$element."')"; ?>">
                            <img src='../static/images/rename.png' height=30px style='text-align: center' 
                                title="Ordner umbenennen"/>
                        </a>
                        <a <?php echo "href=index.php?folder=".urlencode($element).""; ?>><?php
                        echo "<img src='../static/images/green.png' class='img-responsive img-thumbnail' 
                            width=250px style='text-align: center';/>";
                        $n = explode("/", $element);
                        echo "<br>".$n[count($n)-1];
                        ?>
                        </a><br/></a>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="pagination" style="background-color: rgba(255, 208, 0, 0.75); text-align: center;
                 width: 100%; font-size:15px;">
                    <span style="text-align: center;" class="step-links">
                            <a href="<?php echo "?folder=".$query["folder"]."&pth=1";?>">&laquo; Anfang</a>
                            <a href="<?php if(count($fls) <= 30){
                                echo "?folder=".$query["folder"]."&pth=1";
                            }elseif($query["pth"] > 1){
                                echo "?folder=".$query["folder"]."&pth=".(int)($query["pth"]-1);
                            }else{
                                echo "?folder=".$query["folder"]."&pth=1";
                            }?>">Vorherige</a>
                        <span class="current"><?php 
                        echo "Seite ".$query["pth"]." von ".(int)((count($fls) / 30)+1) ?>
                        </span>
                            <a href="<?php if(count($fls) <= 30){
                                echo "?folder=".$query["folder"]."&pth=1";
                            }elseif($query["pth"] < (int)((count($fls) / 30)+1)){
                                echo "?folder=".$query["folder"]."&pth=".(int)($query["pth"]+1);
                            }else{
                                echo "?folder=".$query["folder"]."&pth=".(int)((count($fls) / 30)+1);
                            }?>">Nächste</a>
                            <a href="<?php if(count($fls) <= 30){
                                echo "?folder=".$query["folder"]."&pth=1";
                            }else{
                                echo "?folder=".$query["folder"]."&pth=".(int)((count($fls) / 30)+1);
                            }?>">Ende &raquo;</a>
                    </span>
            </div>
            <?php
            $table = array();
            $row = array();
            $fls = $structure;
            $i = 0;
            foreach($fls as $fl){
                    $give = str_replace($dir."/static/", "", urldecode($fl));
                    array_push($row, $fl);
                    if(($i+1) % 3 == 0){
                        array_push($table, $row);
                        $row = array();
                }
                $i++;
            }
            array_push($table, $row);
            ?>
            <p style="text-align: center; background-color: aqua;">Dateien</p>
            <div style="width:100%; height: 20px;" id="container" class="container" >
            </div>

<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2>Datei verschieben</h2>
    </div>
    <div class="modal-body">
        <p>Zielverzeichnis wählen</u>
        <div id="dirs" style="height:200px;overflow:scroll;padding:5px;border:4px double #DEBB07;">
            <?php
            foreach(get_possible_folders($query["folder"]) as $d){
                echo "<a name='fold'>$d</a><br>";
            }
            ?>
        </div>
    </div>
    <div class="modal-footer">
      <button class="close">Abbrechen</button>
    </div>
  </div>

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
                let folder = "<?php echo $query["folder"];?>";
                getresult('dynamic_gallery.php?elements='+loaded_img+'&id=1&folder='+folder, repeat);
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
    </script>
</body>
</html>