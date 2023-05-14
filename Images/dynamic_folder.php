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
function listonlydir($path){
    $elem = array();
    $files = scandir($path);
    foreach($files as $file) {
        if($file == '.' || $file == '..' || !is_dir($path."/".$file)) continue;
        array_push($elem, $file);
    }
    return $elem;
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

$directories = glob($dir."/static/".$contents[$query["id"]]. '/*' , GLOB_ONLYDIR);

$element = $directories[$query["elements"]];

    $element = urldecode($element);
    $rel = str_replace($dir."/static/", "", $element);
    $bo = $element;
    $bo = str_replace("'", "%27", $bo);
?>
<div class="box">
    <?php
    $bo = $element;
    $bo = str_replace("'", "%27", $bo);
    if(is_dir($element)){
        if(count(listdir($element)) == 0 && count(listonlydir($element)) == 0){
    ?>
    <a onclick="<?php echo "actions('delete', '".str_replace("'", "%27", $element)."')"; ?>">
        <img src='../static/images/delete.png' height=30px style='text-align: center' title="Ordner löschen"/>
    </a>
    <?php }} ?>
    <a onclick="<?php echo "actions('rename', '".$contents[$query['id']].", ".str_replace("'", "%27", $element)."')"; ?>">
    <img src='../static/images/rename.png' height=30px style='text-align: center' title="Ordner umbenennen"/>
    </a>
    <a class="pointer-link" name="myBtn" onclick="modal_dir('<?php echo $bo; ?>')">
        <img src='../static/images/move.png' height=30px style='text-align: center' title="Ordner verschieben"/>
    </a>
    <?php
    $dri = search("content", "id, path", "PATH = '".str_replace($dir."/static/", "", $element)."'");
    # array_search(str_replace($dir."/static/", "", $element), $contents);
    if(!$dri)$dri = array_search(str_replace($dir."/static/", "", $element), $contents);
    $rel = $contents[$dri];
    ?>
    <br>
    <?php 
    # $lnk = array_search(str_replace($dir."/static/", "", $element), $contents);
    $t = explode("/", $element);
    echo "<a href='?id=".$dri."' name='".$t[count($t)-1]."'>";
    $d = listdir($element);
    if (count($d) != 0){
        $r = str_replace("'", "%27", str_replace($dir."/static/", "", $d[0]));
        echo "<img src='../static/".$r."' class='img-responsive img-thumbnail' width=".
        calculate_width($d[0])."px style='text-align: center' />";
    }else{
        echo "<img src='../static/images/green.png' class='img-responsive img-thumbnail' width=250px style='text-align: center' />";
    }
    echo "<br>".$t[count($t)-1];
    ?>
    <br/></a>
</div>