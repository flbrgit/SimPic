<?php
include "utils.php";
function add_fav($element, $folder = "NONE"){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("\\", "/", $dir);
    $re = str_replace($dir."/static/", "", $element);
    $mdir = str_replace("\\Images", "", getcwd());
    $mdir = str_replace("\\", "/", $mdir)."/static/";
    $element = urldecode($element);
    $folder = urldecode($folder);
    $statement = $pdo->prepare("INSERT INTO gallery (path, folder) VALUES (:pth, :folder)");
    $statement->execute(array('pth' => str_replace($mdir, "", $element), 'folder' => $folder));
}
function sub_fav($element){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $dir = str_replace("\\Images", "", getcwd());
    $dir = str_replace("\\", "/", $dir);
    $re = str_replace($dir."/static/", "", $element);
    $mdir = str_replace("\\Images", "", getcwd());
    $mdir = str_replace("\\", "/", $mdir)."/static/";
    $element = urldecode($element);
    $statement = $pdo->prepare("DELETE FROM gallery WHERE path = ?");
    $statement->execute(array(str_replace($mdir, "", $element)));
}
function move_fav($element, $folder){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $element = urldecode($element);
    $folder = urldecode($folder);
    $statement = $pdo->prepare("UPDATE gallery SET FOLDER = ? WHERE PATH = ?");
    $statement->execute(array($folder, $element));
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
function move_file($element, $folder){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $element = urldecode($element);
    $folder = urldecode($folder);
    $pu = explode("/", $folder);
    $u = "";
    for($i = 0; $i < count($pu) - 1; $i++){
        if($i == 0) $u .= $pu[$i];
        else $u .= "/".$pu[$i];
    }
    $id = array_search($element, read_db());
    $di = array_search($element, read_gallery());
    $name = $folder."/".explode("/", $element)[count(explode("/", $element)) - 1];
    #echo $element." ".$folder."/".$name."<br>";
    $statement = $pdo->prepare("UPDATE objects SET PATH = ? WHERE ID = ?");
    $statement->execute(array($name, $id));
    if(in_array($element, read_gallery())){
        $info = init();
        $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
        $statement = $pdo->prepare("UPDATE gallery SET PATH = :path WHERE ID = :id");
        $statement->execute(array("path" => $name, "id" => $di));
    }
}
function rename_fav($element){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $element = urldecode($element);
    $element = explode(", ", $element);

    $el = explode("/", $element[0]);
    $e = "";
    for($i = 0; $i < count($el) - 1; $i++){
        $e .= $el[$i]."/";
    }
    # Rename all files beyond this directory
    $beyond = array();
    $sq = "SELECT id, path, folder FROM gallery";
    foreach ($pdo->query($sq) as $row) {
        if(strpos($row["folder"], $element[0]) !== false){
            $beyond[$row["id"]] = $row["folder"];
        }
    }
    $statement = $pdo->prepare("UPDATE gallery SET folder = ? WHERE id = ?");
    foreach($beyond as $pth){
        $statement->execute(array(str_replace($element[0], $e.$element[1], $pth), array_search($pth, $beyond)));
    }
    #print_r(array($e.$element[1], $element[0]));
    $sql = "SELECT id, path, folder FROM gallery WHERE path = '".$element[0]."'";
    foreach ($pdo->query($sql) as $row) {
        $id = $row["id"];
    }
    $statement = $pdo->prepare("UPDATE gallery SET PATH = ? WHERE id = ?");
    $statement->execute(array($e.$element[1], $id));
}
function rename_file($element){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $element = urldecode($element);
    $element = explode(", ", $element);

    $el = explode("/", $element[0]);
    $e = "";
    for($i = 0; $i < count($el) - 1; $i++){
        $e .= $el[$i]."/";
    }
    # Rename all files beyond this directory
    $beyond = array();
    $sq = "SELECT id, path FROM objects";
    foreach ($pdo->query($sq) as $row) {
        if(strpos($row["folder"], $element[0]) !== false){
            $beyond[$row["id"]] = $row["folder"];
        }
    }
    $statement = $pdo->prepare("UPDATE objects SET path = ? WHERE id = ?");
    foreach($beyond as $pth){
        $statement->execute(array(str_replace($element[0], $e.$element[1], $pth), array_search($pth, $beyond)));
    }
    #print_r(array($e.$element[1], $element[0]));
    $sql = "SELECT id, path, folder FROM gallery WHERE path = '".$element[0]."'";
    foreach ($pdo->query($sql) as $row) {
        $id = $row["id"];
    }
    $statement = $pdo->prepare("UPDATE gallery SET PATH = ? WHERE id = ?");
    $statement->execute(array($e.$element[1], $id));
}
function delete_folder($folder){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $folder = urldecode($folder);
    $stmt = "SELECT id, path, folder FROM gallery WHERE folder LIKE '%$folder%'";
    $new = "";
    $old = explode("/", $folder);
    for($i = 0; $i < count($old) - 1; $i++){
        if($i == 0) $new .= $old[$i];
        else $new .= "/".$old[$i];
    }
    foreach($pdo->query($stmt) as $result){
        move_fav($result["path"], $new);
    }
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
function move($path){
    $paths = listonlydir($path);
}

function move_dir($old, $new){
    $dir = str_replace("\\", "/", str_replace("\\Images", "", getcwd()));
    $letters_old = str_split($old);
    $letters_new = str_split($new);
    $letters = "";
    for($i = 0; $i < count($letters_new); $i++){
        if($letters_new[$i] != $letters_old[$i]) break;
        $letters .= $letters_old[$i];
    }
    $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($old), 
        RecursiveIteratorIterator::SELF_FIRST);

    foreach($iterator as $file) {
        if($file->isDir()) {
            $file = realpath($file);
        }
    }
}
function ren($str){
    $info = init();
    $pdo = new PDO('mysql:host='.$info["HOST"].';dbname='.$info["DBNAME"], $info["ROOT"], $info["PASSWORD"]);
    $folder = urldecode($folder);
    $stmt = "SELECT id, path, folder FROM gallery WHERE folder LIKE '%$folder%'";
    $new = "";
    $old = explode("/", $folder);
    for($i = 0; $i < count($old) - 1; $i++){
        if($i == 0) $new .= $old[$i];
        else $new .= "/".$old[$i];
    }
    foreach($pdo->query($stmt) as $result){
        move_fav($result["path"], $new);
    }
}
function logging($message, $level){
    $myfile = fopen("browser.log", "a");
    $txt = $level.":root:".$message."\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}

$query = array();
foreach(explode("&", $_SERVER['QUERY_STRING']) as $i){
    $j = explode("=", $i);
    $query[$j[0]] = $j[1];
}
$info = init();
$checked = $query["path"];
$checked = explode(",", $checked);
if($query["evt"] == "new_dir"){
    try{
        exec($info["PYTHON"]." action.py ".$query["evt"]." ".$query['path'], $o);
        if($o == "success"){
            echo "success";
        }else{
            echo $o[0];
        }
    }catch (Exception $e){
    }
}else if($query["evt"] == "rename"){
    try{

        exec($info["PYTHON"]." action.py ".$query["evt"]." ".$query['path'], $o);
        if($o == "success"){
            echo "success";
        }else{
            echo $o[0];
        }
    }catch (Exception $e){
    }
}else if($query["evt"] == "delete"){
    try{
        foreach($checked as $element){
            exec($info["PYTHON"]." action.py ".$query["evt"]." ".$element, $o);
        }
        if($o == "success"){
            echo "success";
        }else{
            echo $o[0];
        }
    }catch (Exception $e){
    }
}else if($query["evt"] == "add_fav"){
    try{
        foreach($checked as $element){
            add_fav($element);
        }
        echo "success";
    }catch (Exception $e){
    }
}else if($query["evt"] == "sub_fav"){
    try{
        foreach($checked as $element){
            sub_fav($element);
        }
        echo "success";
    }catch (Exception $e){
    }
}else if($query["evt"] == "upload"){
    try{
        exec($info["PYTHON"]." action.py ".$query["evt"]." ".$query['path'], $o);
        if($o == "success"){
            echo "success";
        }else{
            echo $o[0];
            // print_r($o);
        }
    }catch (Exception $e){
    }
}else if($query["evt"] == "new_dir_gal"){
    try{
        add_fav($query["path"], explode("/", $query["path"])[0]);
        echo "success";
    }catch (Exception $e){
    }
}else if($query["evt"] == "move_gal"){
    try{
        foreach($checked as $element){
            $dir = str_replace("\\Images", "", getcwd());
            $dir = str_replace("\\", "/", $dir);
            $element = str_replace($dir."/static/", "", $element);
            $a = explode("+", $element);
            move_fav($a[0], $a[1]);
        }
        echo "success";
    }catch (Exception $e){
    }
}else if($query["evt"] == "rename_gal"){
    try{
        rename_fav($query["path"]);
        echo "success";
    }catch (Exception $e){
    }
}else if($query["evt"] == "delete_gal"){
    try{
        delete_folder($query["path"]);
        sub_fav($query["path"]);
        echo "success";
    }catch (Exception $e){
    }
}else if($query["evt"] == "move"){
    try{
        foreach($checked as $element){
            $mdir = str_replace("\\Images", "", getcwd());
            $mdir = str_replace("/Images", "", $mdir);
            $mdir = str_replace("\\", "/", $mdir)."/static/";
            $element = str_replace($mdir, "", $element);
            $a = explode("+", $element);
            while($a[1][0] == " "){
                $a[1] = substr($a[1], 1);
            }
            move_file($a[0], $a[1]);
            exec($info["PYTHON"]." action.py ".$query["evt"]." ".$element, $o);
        }
        if($o == "success"){
            echo "success";
        }else{
            echo $o[0];
            // print_r($o);
        }
    }catch (Exception $e){
    }
}else if($query["evt"] == "move_dir"){
    $new = explode("+", $checked[0]);
    $newString = substr($new[0], 0, strpos($new[0], "Browser"));
    $d = $new[0]."+".$newString.$new[1];
    # move_dir($new[0], $newString.$new[1]);
    exec($info["PYTHON"]." action.py move_dir_dirs ".$d."  2>&1", $o);
    if($o[0] == "success") exec($info["PYTHON"]." action.py move_dir_files ".$d."  2>&1", $a);
    if($a[0] == "success") exec($info["PYTHON"]." action.py delete ".$new[0], $b);
    if($b[0] == "success"){
            echo "success";
        }else{
            echo $b[0];
            // print_r($o);
        }
}else if($query["evt"] == "change"){
    # move_dir($new[0], $newString.$new[1]);
    exec($info["PYTHON"]." action.py change ".$query["path"]."  2>&1", $o);
    if($o[0] == "success") {
        echo "success";
    }else{
        echo $o[0];
    }
}
?>