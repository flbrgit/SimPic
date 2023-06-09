SortTable.ok = true;
if(!document.getElementsByTagName){
	alert('Browser unterstützt die Sortierfunktion nicht');
	SortTable.ok = false;
}

// Das Element das angezeigt wird, wenn die Spalte abwärts sortiert ist
SortTable.up = String.fromCharCode(9650);
SortTable.alt_up = 'Aufwärts sortieren';

// Das Element das angezeigt wird, wenn die Spalte aufwärts sortiert ist
SortTable.down = String.fromCharCode(9660);
SortTable.alt_down = 'Abwärts sortieren';

// Farbe des Zeichens in der Spaltenüberschrift
SortTable.pointer_color = '#222';

// Die Bezeichnung der Klasse der Tabellen, die sortiert werden sollen
SortTable.className = 'sortable'; 



SortTable.init = function(){
	var t = document.getElementsByTagName('table');
	var ret = [];
	var regEx  = new RegExp('\\b' + SortTable.className + '\\b', 'i');
	for(var i = 0; i < t.length; i++) {
		if(SortTable.ok && t[i].className && regEx.test(t[i].className))
		ret.push(new SortTable(t[i]));
	}
	return ret;
}

function SortTable(theTable) {
	var self = this;
	var DATE_DE = /(\d{1,2})\.(\d{1,2})\.(\d{2,4})|(\d{1,2})\.(\d{1,2})\.(\d{2,4})\s*(\d{1,2}):(\d{1,2})/;
	var zebra = /\bzebra\b/i.test(theTable.className);
	var tableBody = theTable.tBodies[0];
	var header = theTable.tHead;

	// SortTable Eventhandler, Dummy Funktionen
	self.onstart = self.onsort = self.onprogress = function() {};
	
	this.length = function() { return tableBody.rows.length;};
	this.sort = function(spalte) {
		if(spalte < 0) {
			spalte = header.rows[0].cells.length - 1;
		}
		header.rows[0].cells[spalte].onclick();
	};
	
	if(!header) {
		/**
		 exisitert kein Headerelement:
		 neues Headerelement erzeugen und die erste Zeile dorthin umhängen 
		 Header in die Tabelle einfügen
		*/
		header = theTable.createTHead();
		header.appendChild(tableBody.rows[0]); 
		// Wenn die Tabelle ein tFoot Objekt hat, ist der Body [1]
		tableBody = theTable.tBodies[1] || theTable.tBodies[0];
	}
	
	/**
	Die Headerzeile mit den Events und dem Marker versehen
	**/
	var th = header.rows[0].cells;
	var last_sort;
	
	var offset = 0; // für colspan
	for(var i = 0; i < th.length; i++) {
		// soll die Spalte sortiert werden
		if(th[i].className && /\bno_sort\b/i.test(th[i].className)) continue;
		
		// click Event
		th[i].onclick = ( function() { 
			// Den Zeiger einfügen
			var pointer = document.createElement('span');
			pointer.style.fontFamily = 'Arial';
			pointer.style.fontSize = '80%';
			pointer.style.visibility = 'hidden';
			pointer.innerHTML = SortTable.down;
			th[i].appendChild(pointer);
			
			// Lokale Werte
			var spalte = i + offset;
			var desc = 1;
			var ignoreCase = ((th[i].getAttribute('ignore_case') || th[i].title) == 'ignore_case');
			var forceString = !!(th[i].className && /\bsort_string\b/i.test(th[i].className));
			var locale_de = !!(th[i].className && /\blocale_de\b/i.test(th[i].className));
			
			// und die Eventfunktion
			return function() {
				self.onstart(new Date());
				// Der Aufruf, der eigentlichen Sortierfunktion
				sort(spalte, desc, ignoreCase, forceString, locale_de);
				
				// Sortierung umkehren
				desc = -desc;

				// Den Zeiger der zuletzt geklickten Spalte entfernen
				if(last_sort != pointer) {
					if(last_sort) last_sort.style.visibility = 'hidden';
					pointer.style.visibility = '';
					last_sort = pointer;
				}
				pointer.style.color = SortTable.pointer_color;
				pointer.innerHTML = desc < 0 ? SortTable.down : SortTable.up;
				this.title = desc < 0 ? SortTable.alt_down : SortTable.alt_up;

				self.onsort(new Date());
				
				return false;
				};
		})(); // Funktionsaufruf
		th[i].style.cursor = 'pointer';
		if(th[i].getAttribute('colspan')){
			offset += th[i].getAttribute('colspan') -1;
		}

	}

	/********************************************
	 * Hilfsfunktionen
	 ********************************************/
	function getValue(el, ignoreCase, forceString, locale_de) {
		var val = getText(el).trim();
		if(forceString) return ignoreCase ? val.toLowerCase() : val;
		
		var d = val.match(DATE_DE);
		if(d) {
			if(!d[4]) d[4] = 0; 
			if(!d[5]) d[5] = 0; 
		}
        if(locale_de) val = val.replace(/,/, '.');
        
		return val == parseFloat(val) ? parseFloat(val) : // Zahl
		d ? (new Date(d[3] + '/' + d[2] + '/' + d[1] + ' ' + d[4] + ':' + d[5]).getTime()) :  // deutsches Datum
		!isNaN(Date.parse(val)) ? Date.parse(val) :
		ignoreCase ? val.toLowerCase() : val;
	}

	function getText(td) {
		if(td.getAttribute('my_key')) {
			return td.getAttribute('my_key');
		} else if(td.childNodes.length > 0) {
			// Enthält das Element HTML Knoten
			var input = td.getElementsByTagName('input')[0];
			if(input && input.type == 'text') {
				return input.value;
			} else if(td.getElementsByTagName('select')[0]) {
				return td.getElementsByTagName('select')[0].value;
			} else {
				// Enthält die Zelle HTML Code wird dieser entfernt 
				return td.innerHTML.stripTags();
			}
		} else if(td.firstChild){
				return td.firstChild.data;
		}
		return '';
	}
	
	/*
	Die Sortierfunktion sortiert die angegebene Spalte.
	*/
	function sort(spalte, desc, ignoreCase, forceString, locale_de) { 

		/**
		Die Reihen der Tabelle zwischenspeichern
		*/
		var rows = [];
		var tr = tableBody.rows;
		var tr_length = tableBody.rows.length;

		for(var i = 0; i < tr_length; i++) {
			rows.push(
			{
				elem: tr[i], 
				value: getValue(tr[i].cells[spalte], ignoreCase, forceString, locale_de) 
			});
		}
		// sortieren
		rows.sort( function (a, b) {
			return  a.value.localeCompare ?  desc * a.value.localeCompare(b.value) :
			a.value == b.value ? 0 :
			a.value > b.value ? desc : -desc;
		}
		);

		// umhängen
		var tCopy = tableBody.cloneNode(false);
		for(var i = 0; i < tr_length; i++) {
				if(zebra) {
					rows[i].elem.className = rows[i].elem.className.replace(/( ?odd)/, "");
					if(i % 2) rows[i].elem.className += ' odd' ;
				}
				tCopy.appendChild(rows[i].elem);
				self.onprogress(i, rows[i].elem);
		}
		tableBody.parentNode.replaceChild(tCopy, tableBody);
		tableBody = tCopy;
	}
}

/**
* Entfernt HTML Tags funktioniert nicht, 
* wenn innerhalb der Tags in Attributen HTML Tags die in Anführungszeichen stehen
*
* <a title="Das funktioniert nicht '<br>'">
* <a title="Das funktioniert <br>">
*
*/
String.prototype.stripTags =  function(){
	// remove all string within tags
	var tmp = this.replace(/(<.*['"])([^'"]*)(['"]>)/g, function(x, p1, p2, p3) { return  p1 + p3;});
	// now remove the tags
	return tmp.replace(/<\/?[^>]+>/gi, '');
};