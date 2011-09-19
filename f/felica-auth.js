//create onDomReady Event
window.faOnDomReady = faDomReady;
var faDebugState = false;

//Setup the event
function faDomReady(fn)
{
	//W3C
	if(document.addEventListener)
	{
		document.addEventListener("DOMContentLoaded", fn, false);
	}
	//IE
	else
	{
		document.onreadystatechange = function(){faReadyState(fn)}
	}
}

//IE execute function
function faReadyState(fn)
{
	//dom is ready for interaction
	if(document.readyState == "interactive")
	{
		fn();
	}
}

//execute as soon as DOM is loaded
window.faOnDomReady(faOnReady);

//do on ready
function faOnReady()
{
	faAddEventListner();
}

function faAddEventListner(){
	document.getElementById("felica_auth_debug_flag").addEventListener("click", faDebugMode, false);
	//document.getElementById("felica-auth_debug_flag").click();
}

function faDebugMode(){
	if(faDebugState){
		faDebugState = false;
		document.getElementById("felica_auth_debug_flag").innerText = "Debug On";
		document.getElementById("felica_auth_embed").width = "1";
		document.getElementById("felica_auth_embed").height = "1";
		document.getElementById("felica_auth_object").width = "1";
		document.getElementById("felica_auth_object").height = "1";
		document.getElementById("felica_auth_debug").style.width = "1";
		document.getElementById("felica_auth_debug").style.height = "1";
	}else{
		faDebugState = true;
		document.getElementById("felica_auth_debug_flag").innerText = "Debug Off";
		document.getElementById("felica_auth_embed").width = "280";
		document.getElementById("felica_auth_embed").height = "180";
		document.getElementById("felica_auth_object").width = "280";
		document.getElementById("felica_auth_object").height = "180";
		document.getElementById("felica_auth_debug").style.width = "280";
		document.getElementById("felica_auth_debug").style.height = "180";
	}
}

function faSetDeviceStateStandBy(){
	document.getElementById("felica_auth_device_state").value = "FeliCa Standby.";
}

function faSetDeviceStateDisconnected(){
	if (document.getElementById("felica_auth_identifier").value == ""){
		document.getElementById("felica_auth_device_state").value = "FeliCa Disconnected.";
	}
}

function faSetDeviceStateFalied(){
	document.getElementById("felica_auth_device_state").value = "FeliCa Failed.";
}

function faSetAuthIdentifier(key){
	document.getElementById("felica_auth_identifier").value = key;
	document.getElementById("felica_auth_device_state").value = "FeliCa Detected.";
}