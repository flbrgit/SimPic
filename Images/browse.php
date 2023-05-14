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
function clear($str){
    $str = urldecode($str);
    return str_replace("\\", "", str_replace("'", "", $str));
}
$contents = read_database();
$query = array();
foreach(explode("&", $_SERVER['QUERY_STRING']) as $i){
    $j = explode("=", $i);
    $query[$j[0]] = $j[1];
}
$results = fetch_results($query["tag"]);
?>
<html lang="de">
<head>
  <title style="font-family: 'AntonZora';">SimPic</title>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0," name="viewport">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
  <link rel="stylesheet" href="../static/css/bootstrap.min.css" crossorigin="anonymous">
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
<h1 style="background-color: chartreuse;text-align: center;">Suchergebnisse für: 
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
    echo '"'.urldecode($query["tag"]).'"';
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
 html { overflow-y: scroll; }
        </style>
    <?php
    $dir = str_replace("\\Images", "", getcwd());
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
        return $elem;**/
        $elem = array();
        $files = scandir($path);
        return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG}", GLOB_BRACE);
    }
    function calculate_width($path){
        try{
            // $path = str_replace("'", "%27", $path);
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
    function search($str){
        $contents = read_database();
        $path = '../static/';
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST);
        if(count((array)$items) > 90){
            $ie = array();
            $r = 0;
            foreach($items as $it){
                if($r > 89){
                    break;
                }
                $ie[] = $it;
                $r++;
            }
            $items = $it;
        }
        foreach($items as $item) {
            $item = str_replace("..", ".", $item);
            $item = str_replace("\\.", "", $item);
            $item = str_replace("\\", "/", $item);
            $item = str_replace("./", "", $item);
            # $it = explode("/", $item);
            # $item = $it[count($it)-1];
            if(!in_array($item, $contents) && !is_dir($item)){
                $contents[] = $item;
            }
        }
    
        // get the q parameter from URL
        $q = $str;
    
        $hint = "";
    
        function is_part($needle=string, $haystack=string){
            /**
             * Search a substring in a string.
             */
            $list_haystack = str_split($haystack);
            $list_needle = str_split($needle);
            $start = false;
            foreach ($list_haystack as $item){
                if($item == $list_needle[0] and $start === false){
                    $start = 0;
                }
                if($start !== false){
                    if(count($list_needle) == $start){
                        return true;
                    }
                    if($item != $list_needle[$start]){
                        $start = false;
                    }else{
                        $start += 1;
                    }
                }
            }
            if ($start !== false){
                if(count($list_needle) == $start + 1){
                    return true;
                }
            }
            return false;
        }
    
        // lookup all hints from array if $q is different from ""
        if ($q !== "") {
        $q = strtolower($q);
        $len=strlen($q);
        $yet = array();
        $contents = array_unique($contents);
        $c = 0;
        foreach($contents as $name) {
            if ($c > 90){
                break;
            }
            $path = $name;
            $t = explode("/", $name);
            $name = strtolower($t[count($t)-1]);
            $name = urldecode($name);
            $q = urldecode($q);
            if (strpos($name, $q) || is_part($q, $name) || stristr($name, $q)) {
            if (is_dir($path) || array_search($path, $yet)){
                continue;
            }
            $yet[] = $path;
            if ($hint === "") {
                $hint = str_replace("static/", "", $path);
            } else {
                $hint .= ", ".str_replace('static/', '', $path);
            }
            $c++;
            }
        }
        }
        return $hint;
    }
    
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
    
    $current = $dir."/static/".$contents[$query["id"]];
    update_database($current);
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
            echo "Bilder: <p id='num_img'>".count($results)."<p>";
            echo "Zeige: <p id='show_img'>0<p>";
            echo "Suche";
            ?>
            <script>
            text = '';
            //elem.addEventListener("keydown", TasteGedrückt );
            </script>
            <input class="form-control" placeholder="Suche.." type="text" onkeypress="results(event, this.value)" id="search" />
            <!--<input placeholder="Suche.." type="text" onkeyup="showHint(this.value)" onkeypress="results(event, this.value)" id="search" />
            <p><span id="txtHint"></span></p>-->
            <?php
            $ok = str_replace("'", "%27", $dir."/static/".$contents[$query['id']]);
            ?>
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
        <div style="width:100%; height: 20px;" id="container" class="container">
        </div>
    </div>
    </div>
    </div>
    <script type="text/javascript">
        check_none();
        var loaded_img = 0;
        
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
        
        function scroll(repeat){
            if(get_all() > loaded_img) {
                getresult('dynamic_browse.php?elements='+loaded_img+'&tag=<?php echo $query["tag"]; ?>', repeat, "container", "browse");
            }create_grid("browse");
            let elem = document.getElementById("show_img");
            elem.innerHTML = document.getElementsByClassName("box").length;
        };

        window.onscroll = function (e) {
            let myDiv = document.getElementById('whole_page');
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight)  {
                for(let i=0; i< 2;i++) {
                    scroll(1);
                    loaded_img++;
                }
            }
        }

        window.addEventListener("load", function (e) {
            let myDiv = document.getElementById('whole_page');
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight)  {
                for(let i=0; i< 10;i++) {
                    scroll(1);
                    loaded_img++;
                }
            }
        });
        var $grid = $('.container').packery({});

        $grid.on( 'append.infiniteScroll', function( event, response, path, items ) {
        // layout Packery after each image loads
        $grid.imagesLoaded().progress( function() {
            $grid.packery('layout');
        });
    });
        // document.addEventListener('DOMContentLoaded', setTimeout(create_grid, 500), false);
    </script>
    </div>
</body>
</html>