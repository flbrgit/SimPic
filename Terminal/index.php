<?php
function read_database(){
    $pdo = new PDO('mysql:host=localhost;dbname=Browser', 'root', '');
    $sql = "SELECT id, path, visited FROM content";
    $contents = array();
    foreach ($pdo->query($sql) as $row) {
        $contents[$row["id"]] = $row['path'];
    }
    return $contents;
}
$contents = read_database();
function settings(){
    $myfile = fopen("../static/settings.json", "r");
    $settings = fread($myfile, 2048);
    fclose($myfile);
    return json_decode($settings, true);
}
$settings = settings();
?>
<html lang="de">
<head>
  <title style="font-family: 'AntonZora';">Browser.de</title>
  <meta charset="utf-8">
  <meta content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" name="viewport">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <!-- Add additional CSS in static file -->
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="stylesheet" href="../static/css/styles_browser.css">
  <link rel="shortcut icon" href="../static/images/favicon.ico" />
  <script type="text/javascript" src="../static/javascript/ltrim.js"></script>
    <script type="text/javascript" src="../static/javascript/sort_table.js"></script>
    <script type="text/javascript" src="../static/javascript/action.js"></script>
    <script type="text/javascript" >
    window.onload = function() {
        SortTable.init();
    }
    </script>
<h1 style="background-color: chartreuse;text-align: center;">Terminal</h1>
</head>
<body class=".school_body">
    <style>
        body {
          background-color: green;
        }
        </style>
    <?php
    function listdir($path){
        $ds = glob($path. '/*' , GLOB_ONLYDIR);
        $files = scandir($path);
        $elem = array();
        foreach($files as $file) {
            if($file == '.' || $file == '..' || in_array($path."/".$file, $ds)) continue;
            array_push($elem, $file);
        }
        return $elem;
    }
    function calculate_width($path){
        list($width, $height) = getimagesize($path);
        $rel = $height / $width;
        if($height <= $width){
            return 300;
        }else{
            return (int) (300 / $rel);
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
        <div class="w3-bar w3-black">
            <button class="w3-bar-item w3-button tablink w3-red" onclick="openCity(event,'Einstellungen')">Einstellungen</button>
        </div>
        
        <div id="Einstellungen" class="w3-container w3-border city">
            <h2>Einstellungen</h2>
            <p>Hier können grundlegende EIgenschaften des Browsers geändert werden.</p>
            <div style="overflow-x:auto;width:100%;border: 1px solid black;text-align:center">
                <table style="border: 1px solid black;width:100%">
                    <tr style="border: 1px solid black;">
                        <th style="border: 1px solid black;">Name</th>
                        <th style="border: 1px solid black;">Einstellung</th>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Python- Interpreter";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["python-interpreter"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Seitengröße";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["page-size"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Id-Nummer";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["id_number"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Pfad";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["base_dir"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Ordner löschen";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["delete-folders"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Dateien löschen";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["delete-files"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Dateien verschieben";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["move-files"];?></td>
                    </tr>
                    <tr style="border: 1px solid black;">
                        <td style="border: 1px solid black;"><?php echo "Dateien umbenennen";?></td>
                        <td style="border: 1px solid black;"><?php echo $settings["rename-files"];?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    </div>
    </div>
</body>
</html>