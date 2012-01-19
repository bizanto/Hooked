// JavaScript Document
var mtpVerticalSlide;

function getCookie(c_name)
{
   if (document.cookie.length>0)
     {
     c_start=document.cookie.indexOf(c_name + "=");
     if (c_start!=-1)
       { 
       c_start=c_start + c_name.length+1; 
       c_end=document.cookie.indexOf(";",c_start);
       if (c_end==-1) c_end=document.cookie.length;
       return unescape(document.cookie.substring(c_start,c_end));
       } 
     }
   return "no";
}

function setCookie(name,value,days)
{
   if (days) {
      var date = new Date();
      date.setTime(date.getTime()+(days*24*60*60*1000));
      var expires = "; expires="+date.toGMTString();
   }
   else var expires = "";
   document.cookie = name+"="+value+expires+"; path=/";
}

function checkCookie()
{
   showrightpane=getCookie('showrightpane');
   if (showrightpane!=null && showrightpane!="")
     {
     
     }
     else 
     {
     setCookie('showrightpane','yes',365); //set the default cookie value here
     }
}




window.addEvent('domready', function() {  
	 	mtpVerticalSlide = new Fx.Slide('mail_this_link_form');
		  //Check cookie for previous setting and hide element if required
		checkCookie();
        if (showrightpane=='no') {

		   mtpVerticalSlide.hide();
		}
		$('mail_this_link').addEvent('click', function(e){
			e = new Event(e);//create a new event here
		    e.stop();
		    mtpVerticalSlide.toggle();
	   
		
	       showrightpane=getCookie('showrightpane');
		  if (showrightpane=='yes') {
			 setCookie('showrightpane','no',365);
		  } else {
			 setCookie('showrightpane','yes',365);
		  }
		  
		});

									 
									 
});