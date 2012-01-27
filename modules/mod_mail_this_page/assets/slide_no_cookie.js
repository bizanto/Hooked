// JavaScript Document
var mtpVerticalSlide;


window.addEvent('domready', function() {  
	 	mtpVerticalSlide = new Fx.Slide('mail_this_link_form');
	    mtpVerticalSlide.hide();

		$('mail_this_link').addEvent('click', function(e){
			e = new Event(e);//create a new event here
		    e.stop();
		    mtpVerticalSlide.toggle();
		});
									 
});