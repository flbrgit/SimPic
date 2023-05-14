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
        xmlhttp.open("GET", "search.php?tag="+str+"&name="+getParameterByName("id"), true);
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
                window.top.location.href = url + "/" + "image.php?id="+id+"&name="+filepath[filepath.length-1];
            }
        }
}

function results(event, str){
    if (event.keyCode == 13){
        window.location.href = (window.location.pathname).replace("browse.php", "").replace("index.php", "").replace("image.php", "") + "browse.php?tag="+str;
    }
}

function actions(evt, path){
    path = decodeURI(path);
    let elements = document.getElementsByName("file_checkbox");
    let checked = [];
    for(let i = 0; i < elements.length; i++){
        if(elements[i].checked == true && !(elements[i] in checked)){
            checked.push(elements[i].id);
        }
    }if(checked.length == 0){
        checked.push(path);
    }
    let xmlhttp = new XMLHttpRequest();
    let origin = window.top.location.href;
    xmlhttp.onreadystatechange = function(){
        if (this.readyState == 4 && this.status == 200){
            if (this.responseText == "success"){
                // alert("Directory "+path+" successfully created.");
                if(origin == window.top.location.href){
                    window.top.location.reload();
                    // document.addEventListener('DOMContentLoaded', setTimeout(create_grid, 500), false);
                }
            }else{
                alert("ERROR: "+this.responseText);
                /**
                    let origin = document.getElementById("alert");
                    let child = document.createElement("div");
                    child.setAttribute("class", "alert alert-warning alert-dismissible");
                    let a = document.createElement("a");
                    a.setAttribute("href", "#");
                    a.setAttribute("class", "close");
                    a.setAttribute("data-dismiss", "alert");
                    a.setAttribute("aria-label", "close");
                    a.innerHTML = "&times;";
                    //<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    let txt = document.createElement("p");
                    txt.innerHTML = "ERROR: "+this.responseText;
                    child.appendChild(a);
                    child.appendChild(txt);
                    origin.appendChild(child); */
            }
        }
    }
    if(evt == "new_dir"){
        let d = "\\";
        while(true){
            d = prompt("Bitte Ordnernamen wählen", "Neuer Ordner");
            if(d != null && check_allowed(d)){
                checked = path + "/" + d;
                break;
            }else if(!check_allowed(d)){
                alert("Ein Dateiname darf keines der folgenden Zeichen enthalten: \n"+
                "\"'\\/*:?&$<>|,");
            }else{
                return;
            }
        } 
    }else if(evt == "rename"){
        let d = "\\";
        while(true){
            d = prompt("Bitte Ordnernamen wählen", "Neuer Ordner");
            if(d != null && check_allowed(d)){
                checked = path + ", " + d;
                break;
            }else if(!check_allowed(d)){
                alert("Ein Dateiname darf keines der folgenden Zeichen enthalten: \n"+
                "\"'\\/*:?&$<>|");
            }else{
                return;
            }
        } 
    }else if(evt == "rename_file"){
        let d = "\\";
        while(true){
            d = prompt("Bitte Dateinamen wählen", "test.jpg");
            if(d != null && check_allowed(d)){
                if(!d.split(".")[-1] in [".jpg", ".png", ".PNG", ".JPG"]){
                    d += ".jpg";
                }
                checked = path + ", " + d;
                break;
            }else if(!check_allowed(d)){
                alert("Ein Dateiname darf keines der folgenden Zeichen enthalten: \n"+
                "\"'\\/*:?&$<>|");
            }else{
                return;
            }
        } 
    }else if(evt == "delete"){
        if (checked.length == 1){
            tmp = get_tmp(path);
            let d = confirm("Wollen Sie dieses Objekt wirklich löschen?\n"+tmp);
            if(d != true){
                return;
            }
        }else{
            tmp = get_tmp(path);
            let d = confirm("Wollen Sie diese "+checked.length+" Objekte wirklich löschen?\n"+tmp);
            if(d != true){
                return;
            }
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
        /**
         * 
        let fls = document.getElementById("select_folder").files;
        alert(fls[0].name);
        let a = fls[0].name.split("/");
        let b = "";
        for(let i = 0; i < a.length; i++){
            if (i == 0) b += a[i];
            else b += "/"+a[i];
        }
        alert(b);
         */
        tmp = get_tmp(path);
        let d = prompt("Bitte Pfad eingeben", "");
        d = d.replace("\\", "/");
        if(d != null){
            checked = path + ", " + d;
        }else{
            return;
        }
    }else if(evt == "reload"){
        alert(path);
        return;
    }else if(evt == "new_dir_gal"){
        let d = "\\";
        while(true){
            d = prompt("Bitte Ordnernamen wählen", "Neuer Ordner");
            if(d != null && check_allowed(d)){
                checked = path + "/" + d;
                break;
            }else if(!check_allowed(d)){
                alert("Ein Dateiname darf keines der folgenden Zeichen enthalten: \n"+
                "\"'\\/*:?&$<>|");
            }else{
                return;
            }
        } 
    }else if(evt == "delete_gal"){
        tmp = get_tmp(path);
        let d = confirm("Wollen Sie dieses Objekt wirklich löschen?\n"+tmp);
        if(d != true){
            return;
        }
    }else if(evt == "rename_gal"){
        let d = "\\";
        while(true){
            d = prompt("Bitte Ordnernamen wählen", "Neuer Ordner");
            if(d != null && check_allowed(d)){
                checked = path + ", " + d;
                break;
            }else if(!check_allowed(d)){
                alert("Ein Dateiname darf keines der folgenden Zeichen enthalten: \n"+
                "\"'\\/*:?&$<>|");
            }else{
                return;
            }
        } 
    }else if(evt == "move"){
        let searched = path.split("+")[1];
        for(let b = 0; b < checked.length; b++){
            if (checked[b] != path){
                checked[b] += "+"+searched
            }
        }
    }else if(evt == "move_gal"){
        let searched = path.split("+")[1];
        for(let b = 0; b < checked.length; b++){
            if (checked[b] != path){
                checked[b] += "+"+searched
            }
        }
    }else if(evt == "move_dir"){
        let searched = path.split("+")[1];
        for(let b = 0; b < checked.length; b++){
            if (checked[b] != path){
                checked[b] += "+"+searched
            }
        }
    }else if(evt == "change"){        
        if (checked.length >= 2){
            let d = confirm("Dateien tauschen\r\rWollen Sie die markierten Dateien tauschen?");
            if(d != true){
                return;
            }else{
                let a = "";
                for(let i = 0; i < checked.length; i++) a += checked[i] + ", ";
                checked = a;
            }
        }else{
            alert("Bitte mindestens zwei Dateien auswählen");
            return;
        }
    }
    xmlhttp.open("GET", "../Images/action.php?evt="+evt+"&path="+checked, true);
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

function modal(origin){
    /** // Get the modal
    var modal = document.getElementById("myModal");
            modal.style.display = "block";

    var file = document.getElementsByClassName("modal-header");

    var nm = document.createElement("p");
    nm.innerHTML = origin.split("/")[origin.split("/").length-1];
    nm.setAttribute("name", "filename");
    file[0].appendChild(nm);
    let folders = document.getElementsByName("fold"); */
    let d = prompt("Bitte Zielverzeichnis wählen", "Browser");
    d = d.replace("\\", "/")
    if(d != null) actions("move", origin+"+"+d);
    /**
    for(let u = 0; u < folders.length; u++){
        folders[u].setAttribute("onclick", "actions('move', '"+origin+"+"+folders[u].innerHTML+"')");
    }
    
    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close");

    for(let h = 0; h < span.length; h++){
        // When the user clicks on <span> (x), close the modal
        span[h].onclick = function() {
            let i = document.getElementsByName("filename");
            for(let j = 0; j < i.length; j++){
                let p = i[j].parentElement;
                p.removeChild(i[j]);
            }
            modal.style.display = "none";
        }
    }
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
    if (event.target == modal) {
        let i = document.getElementsByName("filename");
        for(let j = 0; j < i.length; j++){
            let p = i[j].parentElement;
            p.removeChild(i[j]);
        }
        modal.style.display = "none";
    }
    } */
}

function modal_gal(origin){
    // Get the modal
    var modal = document.getElementById("myModal");
            modal.style.display = "block";

    var file = document.getElementsByClassName("modal-header");

    var nm = document.createElement("p");
    nm.innerHTML = origin.split("/")[origin.split("/").length-1];
    nm.setAttribute("name", "filename");
    file[0].appendChild(nm);

    let folders = document.getElementsByName("fold");
    for(let u = 0; u < folders.length; u++){
        folders[u].setAttribute("onclick", "actions('move_gal', '"+origin+"+"+folders[u].innerHTML+"')");
    }

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close");

    for(let h = 0; h < span.length; h++){
        // When the user clicks on <span> (x), close the modal
        span[h].onclick = function() {
            let i = document.getElementsByName("filename");
            for(let j = 0; j < i.length; j++){
                let p = i[j].parentElement;
                p.removeChild(i[j]);
            }
            modal.style.display = "none";
        }
    }
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
    if (event.target == modal) {
        let i = document.getElementsByName("filename");
        for(let j = 0; j < i.length; j++){
            let p = i[j].parentElement;
            p.removeChild(i[j]);
        }
        modal.style.display = "none";
    }
    }
}

function modal_dir(origin){    
    let d = prompt("Bitte Zielverzeichnis wählen", "Browser");
    d = d.replace("\\", "/")
    actions("move_dir", origin+"+"+d);
}

function check_all(){
    let elements = document.getElementsByName("file_checkbox");
    for(let i = 0; i < elements.length; i++){
        elements[i].checked = true;
    }
    let element = document.getElementById("selection_counter");
    element.innerHTML = elements.length+" ausgewählt";
}

function check_none(){
    let elements = document.getElementsByName("file_checkbox");
    for(let i = 0; i < elements.length; i++){
        elements[i].checked = false;
    }
    let element = document.getElementById("selection_counter");
    element.innerHTML = "0 ausgewählt";
}

function set_checked(id){
    let element = document.getElementById(id);
    element.checked = true;
}

function delete_checked(id){
    let element = document.getElementById(id);
    element.checked = false;
}

function move(d="myBar") {
    var elem = document.getElementById(d);   
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

function check_allowed(str){
    let forbidden = ["\"", "'", "\\", "/", "*", ":", "?", "&", "$", "<", ">", "|", ","];
    if(str == null) return true;
    for(let i = 0; i < forbidden.length; i++){
        if(str.includes(forbidden[i])){
            return false;
        }
    }
    return true;
}

function form_files(){
    let name = document.getElementById("file-input").value;
    alert(name);
}

function control_checked(){
    let elements = document.getElementsByName("file_checkbox");
    let counter = 0;
    for(let i = 0; i < elements.length; i++){
        if(elements[i].checked == true){
            counter++};
    }
    let e = document.getElementById("selection_counter");
    e.innerHTML = counter+" ausgewählt";
}

function get_checked(){
    let elements = document.getElementsByClassName("box");
    return elements.length;
}

function get_img_ids(){
    let elements = document.getElementsByClassName("img_name");
    let e = [];
    for(let i = 0; i < elements.length; i++){
        e.push(elements[i].name);
    }
    return e;
}

function get_all(){
    let element = document.getElementById("num_img");
    return parseInt(element.innerHTML);
}

function move_on_scroll(){
    window.onscroll = function (e) {
        if(true){
            var vertical_position = 0;
            if (pageYOffset)//usual
                vertical_position = pageYOffset;
            else if (document.documentElement.clientHeight)//ie
                vertical_position = document.documentElement.scrollTop;
            else if (document.body)//ie quirks
                vertical_position = document.body.scrollTop;
            
            var your_div = document.getElementById('sidebar');
            var my_div = document.getElementById('whole_page');
            if(vertical_position > my_div.offsetHeight) vertical_position -= your_div.offsetHeight;
            your_div.style.top = (vertical_position) + 'px';
            your_div.style.bottom = (my_div.offsetHeight - vertical_position) + "px";
            your_div.style.maxHeight = (my_div.offsetHeight - your_div.offsetHeight) + "px";
        }
    }
}

function scrolled(e) {
    var your_div = document.getElementById('whole_page');
    var my_div = document.getElementById('sidebar');
    if (pageYOffset < your_div.style.top) {
      return true;
    }
    return false;
  }

function move_scroll(){
    $(window).scroll(function(){
        $("#sidebar").stop().animate({"marginTop": ($(window).scrollTop()) + "px", "marginLeft":($(window).scrollLeft()) + "px"}, "slow" );
      });
}

function Sleep(milliseconds) {
    return new Promise(resolve => setTimeout(resolve, milliseconds));
}

function create_grid(type = "normal"){
    try{
        var elem = document.getElementById('container');
        var pckry = new Packery( elem, {
            // options
        itemSelector: '.box',
        percentPosition: true,
        });
        pckry.layout();
          
    }catch (e){//console.error(e.message, e.name);
    }
    if(type!="browse")
    {
    try{
        var elem = document.querySelector('.container_folder');
        var pckry = new Packery( elem, {
        // options
        itemSelector: '.box',
        percentPosition: true,
        });
        //imagesLoaded( grid ).on( 'progress', function() {
        //    pckry.layout();
        //  });  
    }catch(e){//console.error(e.message, e.name);
    }}
}

function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function getresult(url, repeat, addon = "container", type = "normal") {
    for(let i=0; i<1;i++){
        $.ajax({
            url: url,
            type: "GET",
            data:  {id:getParameterByName('id', url),
                    elements:getParameterByName('elements', url),
                    tag:getParameterByName('tag', url),
                    folder:getParameterByName('folder', url)},
            success: function(data){
                let e = document.getElementById(addon);
                e.append($.parseHTML(data)[0]);
                create_grid(type=type);
                // $("#container").append(data);
            },
            error: function(data){
                //alert(JSON.stringify(data, null, 4));
            } 	        
        });
    }
}
