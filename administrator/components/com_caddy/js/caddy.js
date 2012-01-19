	function deleteRow() {
		var numrows=99;
		var t=document.getElementById("mine");
		for (i=numrows; i >= 0; i--) {
			cbid="tid"+i;
			cb=document.getElementById(cbid);
			if ((cb) && (cb.checked == true)){
				pn=cb.parentNode.parentNode.rowIndex;
				t.deleteRow(pn);
			}
		}
		var t=document.getElementById("mine");
		var n=t.rows.length;
		document.getElementById("rows").value = n-1;
	}

	function getSelected(allbuttons){
		for (i=0;i<allbuttons.length;i++) {
			if (allbuttons[i].checked) {
				return allbuttons[i].value
			}
		}
		return null;
	}

	
	function boxchecked(obj){
		var rownum=obj.value;
		if (obj.checked == true) {
			alert(rownum);	
		}
	}
	
	function addRow() {
		var t=document.getElementById("mine");
		var maxrows=t.rows.length;
		var y=t.insertRow(maxrows);
		var ch=document.createElement("td");
		var c0=document.createElement("td");
		var c1=document.createElement("td");
		var c2=document.createElement("td");
		var c3=document.createElement("td");
		var c4=document.createElement("td");
		var c5=document.createElement("td");
		
		ch.width=20;
		c0.width=40;
		c1.width=40;
		c2.width=40;
		c3.width=80;
		c5.width=20;
		c4.frame="void";
		

		var h0=document.createElement("input");
		h0.name="tid"+maxrows;
		h0.id="tid"+maxrows;
		h0.type="checkbox";
		h0.value=maxrows;
		h0.width=20;
		h0.checked=false;
//		h0.onclick=boxchecked(h0);
		
		var i4=document.createElement("input");
		i4.name="optionid[]";
		i4.type="hidden";
		i4.value="";
		var i0=document.createElement("input");
		i0.name="optionshorttext[]";
		i0.type="text";
		i0.value="";
		i0.size=30;
		var i1=document.createElement("input");
		i1.name="optionformula[]";
		i1.type="text";
		i1.value="";
		var i2=document.createElement("input");
		i2.name="optioncaption[]";
		i2.type="text";
		i2.value="";
		var i5=document.createElement("input");
		i5.name="optiondisporder[]";
		i5.type="text";
		i5.value="0";
        i5.size=1;
		var i3=document.createElement("input");
		i3.name="optiondefselect";
		i3.type="radio";
		i3.value=maxrows - 1; // remove 1 for the header

		ch.appendChild(h0);
		c0.appendChild(i0);
		c0.appendChild(i4);
		c1.appendChild(i1);
		c2.appendChild(i2);
		c3.appendChild(i5);
		c5.appendChild(i3);

		y.appendChild(ch);
		y.appendChild(c0);
		y.appendChild(c1);
		y.appendChild(c2);
		y.appendChild(c3);
		y.appendChild(c5);
		y.appendChild(c4);

		document.getElementById("rows").value=maxrows;
		return true;
	}
 