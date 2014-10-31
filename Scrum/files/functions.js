function drag(ev) {

	ev.dataTransfer.setData("Text",ev.target.id);
}

function drop(ev) {

	ev.preventDefault();
	var data = ev.dataTransfer.getData("Text");

	//If the target element is not a scrum column, cancel the drop event
	if (ev.target.id.toString().indexOf("scrumcolumn") != -1){
		ev.target.appendChild(document.getElementById(data));
		sendData(data, ev.target.getAttribute("columnstatus"));
	}
}

function allowDrop(ev) {

	ev.preventDefault();
}

function sendData(element, columnstatus){
	
	var data = {};
	data["bugid"] = document.getElementById(element).getAttribute("bugid");
	data["columnstatus"] = columnstatus;
	var xhr = new XMLHttpRequest();
	xhr.open("GET", "plugins/Scrum/pages/webservice.php?json="+JSON.stringify(data), true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    xhr.send();
}