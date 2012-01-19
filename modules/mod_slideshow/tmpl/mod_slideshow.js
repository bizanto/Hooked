/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

(function ($) {
	$.fn.slideshow = function (options) {
		var settings = {
			autocycle: 1,
			transtime: 5000,
			transtype: 'fade'
		};
		
		if (options) {
			$.extend(settings, options);
		}
	
		var current = 0;
		var slides = $('.hss-slides', this).children();
		var $nav = $('.hss-nav-items li', this);
		var tid;
		var intransit = false;
		
		slides.slice(1).hide();
		$nav.slice(0, 1).addClass('active');
		
		var changeImage = function (i) {
			if (intransit) return;
			intransit = true;
			$(slides[current]).fadeOut(function () {
				$(slides[i]).fadeIn(function () {
					intransit = false;
				});
			});
			
			current = i;
			$nav.removeClass('active');
			$nav.slice(i, i+1).addClass('active');
			
			if (settings.autocycle) {
				window.clearTimeout(tid);
				tid = window.setTimeout(function () {
					changeImage((i+1) % slides.length);
				}, settings.transtime);
			}
		};
		
		$('.hss-nav-items li', this).each(function (idx) {
			$(this).click(function () {
				changeImage(idx);
			});
		});
		
		$('.hss-prev', this).click(function () {
			changeImage((slides.length - (-(current-1))) % slides.length);
			return false;
		});
		
		$('.hss-next', this).click(function () {
			changeImage((current+1) % slides.length);
			return false;
		});
		
		if (settings.autocycle) {
			tid = window.setTimeout(function () {
				changeImage(current+1);
			}, settings.transtime);
		}
		
		return this;
	};
}) (jQuery);
