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
    return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG,webm,mp4}", GLOB_BRACE);
}

function logging($message, $level){
    $myfile = fopen("browser.log", "a");
    $txt = $level.":root:".$message."\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}

$query = array();
$contents = read_database();
$gallery = read_gallery();
$dir = str_replace("\\Images", "", getcwd());
$dir = str_replace("/Images", "", $dir);
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
    array_push($row, $fls[$i]);
}
$element = $row[$query["elements"]];

    $element = urldecode($element);
    $rel = str_replace($dir."/static/", "", $element);
    $bo = $element;
    $bo = str_replace("'", "%27", $bo);
?>
<div class='box'>
    <input class="pointer-link" type="checkbox" name="file_checkbox"
        style="width:20px;height:20px;text-align:left" onclick="control_checked();" id="<?php echo $bo;?>">
    <?php
        if(!in_array($rel, $gallery)){ 
    ?>
    <a class="pointer-link" onclick="<?php echo "actions('delete', '".$bo."')"; ?>">
        <img src='../static/images/delete.png' height=30px style='text-align: center' title="Objekt löschen"/>
    </a>
    <a class="pointer-link" onclick="<?php echo "actions('add_fav', '".$bo."')"; ?>">
        <img src='../static/images/add_favourite.png' height=30px style='text-align: center' title="Zu Favoriten hinzufügen"/>
    </a>
    <?php }else{ ?>
    <a class="pointer-link" onclick="<?php echo "actions('sub_fav', '".$bo."')"; ?>">
        <img src='../static/images/dismiss_favourite.png' height=30px style='text-align: center' title="Aus Favoriten entfernen"/>
    </a>
    <?php } ?>
    <a class="pointer-link" name="myBtn" onclick="modal('<?php echo $bo; ?>')">
        <img src='../static/images/move.png' height=30px style='text-align: center' title="Datei verschieben"/>
    </a>
    <br>
        <a target="_blank" name="<?php echo basename($element);?>" class="img_name
        <?php if(pathinfo($element, PATHINFO_EXTENSION) == "webm") echo "embed-responsive embed-responsive-16by9"; ?>
        " href="image.php?<?php echo "id=".$query["id"]."&file=".$query["file"]."&name=".basename($element);
    ?>">
    <?php
    if(pathinfo($element, PATHINFO_EXTENSION) == "webm"){
        echo "<iframe class='embed-responsive-item' src='../static/".$rel."' title=".basename($element)."></iframe><br/>";
        echo basename($element); 
    }else{
        $ko = str_replace("'", "%27", $contents[$query['id']]);
        echo "<img src='../static/".$rel."' class='img-responsive img-thumbnail' width=".
            calculate_width($element).
            "px style='text-align: center'/><br/>"; 
            echo basename($element); 
    }
    ?>
    </a>
</div>