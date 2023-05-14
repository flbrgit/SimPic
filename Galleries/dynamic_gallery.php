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
function listdir($path){
    return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG}", GLOB_BRACE);
}

function logging($message, $level){
    $myfile = fopen("browser.log", "a");
    $txt = $level.":root:".$message."\n";
    fwrite($myfile, $txt);
    fclose($myfile);
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

$query = array();
$gallery = read_folder($_GET["folder"]);
$contents = [];
foreach($gallery as $row){
    $contents[] = $row;
}
$dir = str_replace("\\Galleries", "", getcwd());
$dir = str_replace("/Galleries", "", $dir);
$dir = str_replace("\\", "/", $dir);
$repeat=1;
$query["id"] = $_GET["id"];
$query["elements"] = $_GET["elements"];
$query["file"] = 1;
$fls = listdir($dir."/static/".$contents[$query["id"]]);
# asort($fls);
$table = array();
$row = array();
for($i = 0; $i < count($fls);$i++){
    $give = str_replace($dir."/static/", "", $fls[$i]);
    array_push($row, $give);
}
$element = $contents[$query["elements"]];
$files = read_database();
    $element = urldecode($element);
    $name = basename($element);
    $rel = str_replace($dir."/static/", "", $element);
    $bo = $dir."/static/".$element;
    $bo = str_replace("'", "%27", $bo);
    $di = array_search(dirname($element), $files);
?>
<div class='box'>
    <input class="pointer-link" type="checkbox" name="file_checkbox"
        style="width:20px;height:20px;text-align:left" onclick="control_checked();" id="<?php echo $bo;?>">
    <a class="pointer-link" onclick="<?php echo "actions('sub_fav', '".$bo."')"; ?>">
        <img src='../static/images/dismiss_favourite.png' height=30px style='text-align: center' title="Aus Favoriten entfernen"/>
    </a>
    <a name="myBtn" onclick="modal_gal('<?php echo $bo;?>')">
        <img src='../static/images/move.png' height=30px style='text-align: center' title="Datei verschieben"/>
    </a>
    <br>
        <a target="_blank" name="<?php echo basename($element);?>" class="img_name" href="../Images/image.php?<?php echo "id=".$di."&file=".$query["file"]."&name=".basename($element);
    ?>">
    <?php
        $ko = str_replace("'", "%27", $contents[$query["elements"]]);
        echo "<img src='../static/".$rel."' class='img-responsive img-thumbnail' width=".
            calculate_width($bo).
            "px style='text-align: center'/><br/>"; 
            echo basename($element); ?>
    </a></div>