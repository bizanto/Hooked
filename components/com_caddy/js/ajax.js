var xmlhttp;
function loadXMLDoc(url, dothis) {
	xmlhttp=null;
	if (window.XMLHttpRequest) {
		xmlhttp=new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	if (xmlhttp!=null) {
		xmlhttp.onreadystatechange=dothis;
		xmlhttp.open("GET",url,true);
		xmlhttp.send(null);
	}
	else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function setVoucherInfo(){
	if (xmlhttp.readyState==4){
		if (xmlhttp.status==200){
			var xmlDoc=xmlhttp.responseText;
			result=xmlDoc.split("|");
			document.getElementById("voucherinfo").innerHTML=result[0];
			document.getElementById("debug").innerHTML=xmlhttp.status + xmlhttp.responseText;
		}
		else
	    {
			document.getElementById("debug").innerHTML=xmlhttp.status + xmlhttp.responseText;
	    }
	}
}


function getVoucherInfo() {
	var voucher=document.getElementById('voucher').value;
	loadXMLDoc("index2.php?option=com_caddy&no_html=1&action=vouchers&task=getvoucherinfo&voucher="+voucher, setVoucherInfo);
}

function getShipCost(region){
	document.checkout.scodrbtn.disabled="true";
	document.getElementById("ajaxloader").style.display="block";
	loadXMLDoc("index2.php?option=com_caddy&no_html=1&action=shipping&task=loadshipcost&shipRegion="+region, updateShipCost);
}

function updateShipCost(){
	if (xmlhttp.readyState==4){
		if (xmlhttp.status==200){
			var xmlDoc=xmlhttp.responseText;
			result=xmlDoc.split("|");
			document.getElementById("shipCost").innerHTML=result[0];
			document.getElementById("scSub").innerHTML=result[1];
			document.getElementById("scTax").innerHTML=result[2];
			document.getElementById("scgTotal").innerHTML=result[3];
			
			document.checkout.scodrbtn.disabled=false;
			document.getElementById("ajaxloader").style.display="none";
		}
		else
	    {
			document.getElementById("debug").innerHTML=xmlhttp.status + xmlhttp.responseText;
	    	
		    alert("Problem retrieving XML data");
	    }
	}
}

function test(){
	alert("oh dear");
}
