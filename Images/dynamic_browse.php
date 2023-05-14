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
    return glob($path."/*.{jpg,png,gif,GIF,JPG,PNG}", GLOB_BRACE);
}

function logging($message, $level){
    $myfile = fopen("browser.log", "a");
    $txt = $level.":root:".$message."\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}

function clear($str){
    $str = urldecode($str);
    return str_replace("\\", "", str_replace("'", "", $str));
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
$query["tag"] = $_GET["tag"];
$element = fetch_results_index($query["tag"], $_GET["elements"]);

    $element = urldecode($element);
    $rel = str_replace($dir."/static/", "", $element);
    $bo = $element;
    $bo = str_replace("'", "%27", $bo);
?>
<div class='box'>
    <input class="pointer-link" type="checkbox" name="file_checkbox"
        style="width:20px;height:20px;text-align:left" onclick="control_checked();" id="<?php echo $bo;?>"><?php
    $pth = explode("/", $element);
    $e = "";
    for($i=0; $i<count($pth)-1; $i++){
        $e .= "$pth[$i]";
        if($i != count($pth)-2){
            $e.="/";
        }
    }
    $name = basename($element);
    $di = array_search($e, $contents);
    $upon = "";
    $a = explode("/", $element);
    for($i = 0; $i < count($a)-1; $i++){
        if(count(str_split($upon)) == 1){
            $upon = $a[$i];
        }else{
            $upon = $upon."/".$a[$i];
        }            
    }
    $dri = array_search($upon, $contents);
    if(!in_array($element, $gallery)){                                
    ?>
    <a onclick="<?php echo "actions('add_fav', '".$element."')"; ?>">
        <img src='../static/images/add_favourite.png' height=30px style='text-align: center' 
            title="Zu Favoriten hinzufügen"/>
    </a>
    <?php }else{ ?>
    <a onclick="<?php echo "actions('sub_fav', '".$element."')"; ?>">
        <img src='../static/images/dismiss_favourite.png' height=30px style='text-align: center' 
            title="Aus Favoriten entfernen"/>
    </a>
    <?php } ?>
    <a href="<?php 
        if(in_array($upon, $contents)){
            $lnk = array_search(str_replace($dir."/static/", "", $upon), $contents);
            $var = array_search($name, listdir($dir."/static/".$upon));
            if($var === NULL){
                $var = 1;
            }else{
                $var = (int)($var / 30) + 1;
            }
            echo "index.php?id=".$di."&file=".$var;
        }else{
            echo "index.php?id=1";
        }?>">
        <img src='../static/images/open_dir.png' height=30px style='text-align: center' 
            title="Dateipfad öffnen"/>
    </a>
    
    <a class="pointer-link" name="myBtn" onclick="modal('<?php echo $bo; ?>')">
        <img src='../static/images/move.png' height=30px style='text-align: center' title="Datei verschieben"/>
    </a>
    <a class="pointer-link" onclick="<?php echo "actions('delete', '".$bo."')"; ?>">
        <img src='../static/images/delete.png' height=30px style='text-align: center' title="Datei löschen"/>
    </a>
    <br>
    <a target="_blank" name="<?php echo basename($element);?>" class="img_name" href="image.php?<?php echo "id=".$di."&file=1&name=".$name; ?>"><?php
    $r = $element;
    echo "<img src='../static/".$r."' class='img-responsive img-thumbnail' width=".
        calculate_width($dir."/static/".$element).
        "px style='text-align: center';/><br/>";
        echo $name; ?>
    </a>
</div>