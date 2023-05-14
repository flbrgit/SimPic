function showHint(str) {
    if (str.length == 0) {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let o = this.responseText;
            o = o.split("|||||");
            let str = o[0];
            let db = o[1];
            db = db.split(", ");
            var lnk = str.split(", ");
            //document.getElementById("txtHint").innerHTML = this.responseText;
            var yet = [];
            var remove = document.querySelectorAll('#suggestion');
            for (i = 0; i < remove.length; i++){
                remove[i].parentElement.removeChild(remove[i]);
            }
            let links = lnk.filter(set);
            //alert(db[0].split("#")[1] + "--" + links[0].split("/").slice(0, links[0].split("/").length-1).join("/"));
            for(i = 0; i < links.length; i++){
                let name = links[i].split("/");
                if (name[name.length-1] in yet || name[name.length-1].split(".").length != 2){
                    continue;
                }
                yet.push(name[name.length-1]);
                let id = getParameterByName("id");
                let path = name.slice(0, name.length-1);
                path = path.join("/");
                for(j = 0; j < db.length; j++){
                    let g = db[j].split("#");
                    if(g[1] == path){
                        id = g[0];
                    }
                }
                let element = document.createElement('a', 'id="suggestion" name="'+links[i]+'" href="image.php?id="' + 
                            id+"&name="+name[name.length-1]);
                element.setAttribute("id", "suggestion");
                element.setAttribute("href", "image.php?id=" + 
                            id+"&name="+name[name.length-1]);
                element.setAttribute("name", links[i]);
                element.innerHTML = name[name.length-1];
                let p = document.createElement('p', 'id="suggestion" name="'+links[i] + 'tag='+id);
                p.setAttribute("id", "suggestion");
                p.setAttribute("name", links[i]);
                p.setAttribute("tag", id);
                p.appendChild(element);
                document.getElementById("txtHint").appendChild(p);
            }
        }
        }
        xmlhttp.open("GET", "../Images/search.php?tag="+str+"&name="+getParameterByName("id"), true);
        xmlhttp.send();
    }
}

function set(value, index, self) {
    return self.indexOf(value) === index;
}

function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function pressed(event) {
        if (event.keyCode == 13){
            let remove = document.querySelectorAll('#suggestion');
            if (remove.length != 0){
                let filepath = remove[0].getAttribute("name").split("/");
                let id = remove[0].getAttribute("tag");
                let path = filepath.slice(0, filepath.length-1);
                path = path.join("/");
                let url = window.top.location.href;
                url = url.split("/").slice(0, url.split("/").length-1).join("/");
                window.top.location.href = url + "/" + "../Images/image.php?id="+id+"&name="+filepath[filepath.length-1];
            }
        }
}

function results(event, str){
    if (event.keyCode == 13){
        let url = window.top.location.href;
        url = url.split("/").slice(0, url.split("/").length-1).join("/");
        window.top.location.href = url + "/" + "../Images/browse.php?tag="+str;
    }
}

function actions(evt, path){
    let xmlhttp = new XMLHttpRequest();
    let origin = window.top.location.href;
    xmlhttp.onreadystatechange = function(){
        if (this.readyState == 4 && this.status == 200){
            if (this.responseText == "success"){
                // alert("Directory "+path+" successfully created.");
                if(origin == window.top.location.href){
                    window.top.location.reload();
                }
            }else{
                alert("ERROR: "+this.responseText);
            }
        }
    }
    if(evt == "new_dir"){
        let d = prompt("Bitte Ordnernamen wählen", "Neuer Ordner");
        if(d != null){
            path = path + "/" + d;
        }else{
            return;
        }
    }else if(evt == "rename"){
        let d = prompt("Bitte Ordnernamen wählen", "Neuer Ordner");
        if(d != null){
            path = path + ", " + d;
        }else{
            return;
        }
    }else if(evt == "rename_file"){
        let d = prompt("Bitte Dateinamen wählen", "test.jpg");
        if(d != null){
            if(!d.split(".")[-1] in [".jpg", ".png", ".PNG", ".JPG"]){
                d += ".jpg";
            }
            path = path + ", " + d;
        }else{
            return;
        }
    }else if(evt == "delete"){
        tmp = get_tmp(path);
        let d = confirm("Wollen Sie dieses Objekt wirklich löschen?\n"+tmp);
        if(d != true){
            return;
        }
    }else if(evt == "add_fav"){
        tmp = get_tmp(path);
        let d = confirm("Wollen Sie dieses Objekt wirklich zu den Favoriten hinzufügen?\n"+tmp);
        if(d != true){
            return;
        }
    }else if(evt == "sub_fav"){
        tmp = get_tmp(path);
        let d = confirm("Wollen Sie dieses Objekt wirklich aus den Favoriten entfernen?\n"+tmp);
        if(d != true){
            return;
        }
    }else if(evt == "upload"){
        tmp = get_tmp(path);
        let d = prompt("Bitte Pfad eingeben", "");
        d = d.replace("\\", "/");
        if(d != null){
            path = path + ", " + d;
        }else{
            return;
        }
    }else if(evt == "reload"){
        alert(path);
        return;
    }
    xmlhttp.open("GET", "../Images/action.php?evt="+evt+"&path="+path, true);
    xmlhttp.send();
}
function get_tmp(path){
    let tmp = path.split("/");
    let i = "";
    for(let j = 0; j < tmp.length; j++){
        if (tmp[j] == "Browser"){
            break;
        }else{
            i += tmp[j] + "/";
        }
    }
    return path.replace(i, "");
}

function move_scroll(){
    $(window).scroll(function(){
        $("#sidebar").stop().animate({"marginTop": ($(window).scrollTop()) + "px", "marginLeft":($(window).scrollLeft()) + "px"}, "slow" );
      });
}

function create_grid(){
        
    var $grid = $('.container').packery({
            percentPosition: true,
    })
    // layout Packery after each image loads
    $grid.imagesLoaded().progress( function() {
    $grid.packery();
    });
}


