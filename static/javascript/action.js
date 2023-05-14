function console_backend(str) {
    if (str.length == 0) {
        document.getElementById("filenumber").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let parent = document.getElementById("console");
            let child = document.createElement('p', 'id="TST"');
            let information = this.responseText.split("+#+");
            let lines = information[information.length-1].split("-#-");
            child.innerHTML = ">>>"+lines[0];
            parent.appendChild(child);
            for(i=1; i<lines.length; i++){
                let child = document.createElement('p', 'id="TST"');
                child.innerHTML = lines[i];
                parent.appendChild(child);
            }
            if(information.length != 1){
                if(information[0] == "bar"){
                    let parent = document.getElementById("progress");
                    let child = document.createElement('div', 'class="w3-container w3-round w3-blue" \
                            style="height:24px;width:0;" id="pro"');
                    child.setAttribute("style", "height:24px;width:0;")
                    child.setAttribute("id", "pro")
                    child.setAttribute("class", "w3-container w3-round w3-blue")
                    parent.appendChild(child);
                    move(d="pro");
                    //parent.removeChild(child);
                }
            }
            parent.scrollTop = parent.scrollHeight;
        }
        }
        xmlhttp.open("GET", "../Terminal/action.php?order="+str, true);
        xmlhttp.send();
    }
}
function rename(str, evt) {
    if (str.length == 0) {
        document.getElementById("filenumber").innerHTML = "";
        return;
    } else {
        if (evt.keyCode == 13){
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("filenumber").innerHTML = this.responseText;
            }
            }
            xmlhttp.open("GET", "terminal.php?q="+str+"&order=rename", true);
            xmlhttp.send();
        }
    }
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