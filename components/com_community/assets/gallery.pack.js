joms.extend({gallery:{inputFocused:false,bindFocus:function(){joms.jQuery("textarea,:text").each(function(){joms.jQuery(this).focus(function(){joms.gallery.inputFocused=true}).blur(function(){joms.gallery.inputFocused=false})})},bindKeys:function(){joms.jQuery(document.documentElement).keyup(function(a){if(!joms.gallery.inputFocused){if(a.keyCode==37){joms.gallery.displayPhoto(joms.gallery.prevPhoto())}else{if(a.keyCode==39){joms.gallery.displayPhoto(joms.gallery.nextPhoto())}}}})},showDeletePhoto:function(a){if(joms.jQuery(a).children(".photo-action")){if(joms.jQuery(a).children(".photo-action").css("display")=="none"){joms.jQuery(a).children(".photo-action").show()}else{joms.jQuery(a).children(".photo-action").hide()}}},confirmRemovePhoto:function(e){var c="";var b=0;if(e==null){var a=joms.gallery.currentPhoto();e=a.id;b=1}var d="jax.call('community', 'photos,ajaxConfirmRemovePhoto', '"+e+"' , '"+c+"','"+b+"');";cWindowShow(d,"",450,100)},removePhoto:function(g,d,c){if(c=="1"){var f=jsPlaylist.photos;var b=joms.gallery.currentPhoto();f.splice(joms.gallery.getPlaylistIndex(b.id),1);var a=(jsPlaylist.photos.length<1)?1:0;if(!a){d="joms.gallery.displayPhoto(joms.gallery.nextPhoto());cWindowHide();"}}var e="jax.call('community', 'photos,ajaxRemovePhoto', '"+g+"', '"+d+"');";cWindowShow(e,"",450,100)},getPlaylistIndex:function(b){if(b==undefined){return 0}var a;joms.jQuery.each(jsPlaylist.photos,function(c){if(this.id==b){a=c}});return a},nextPhoto:function(b){var a=0;if(b!=undefined){a=joms.gallery.getPlaylistIndex(b.id)}else{a=jsPlaylist.currentPlaylistIndex+1;if(a>=jsPlaylist.photos.length){a=0}}return jsPlaylist.photos[a]},prevPhoto:function(b){var a=0;if(b!=undefined){a=joms.gallery.getPlaylistIndex(b.id)}else{a=jsPlaylist.currentPlaylistIndex-1;if(a<0){a=jsPlaylist.photos.length-1}}return jsPlaylist.photos[a]},currentPhoto:function(b){var a=jsPlaylist.currentPlaylistIndex;if(b!=undefined){a=joms.gallery.getPlaylistIndex(b.id);joms.gallery.urlPhotoId(b.id)}if(a==undefined){a=joms.gallery.getPlaylistIndex(joms.gallery.urlPhotoId())}jsPlaylist.currentPlaylistIndex=a;return jsPlaylist.photos[a]},urlPhotoId:function(a){if(a==undefined){var b=document.location.href;if(b.match("#")&&b.split("#")[1].match("photoid=")){b=b.split("#")[1];if(b.match("&")){b=b.split("&")[0]}return b.split("=")[1]}}else{document.location=document.location.href.split("#")[0]+"#photoid="+a}},init:function(){if(typeof(joms.jQuery.isArray)=="undefined"){joms.jQuery.extend({isArray:function(a){return a.constructor==Array}})}joms.gallery.displayViewport();joms.gallery.displayPhoto(joms.gallery.currentPhoto())},displayViewport:function(){var f=joms.jQuery("#cGallery .photoViewport");f.unbind();f.hover(function(){joms.jQuery("#cGallery .photoAction").fadeIn("fast")},function(){joms.jQuery("#cGallery .photoAction").fadeOut("fast")});var c=joms.jQuery("#cGallery .photoDisplay");c.css("height",Math.floor(c.width()/16*12));var e=joms.jQuery("#cGallery .photoLoad");e.css({top:Math.floor((c.height()/2)-(e.height()/2)),left:Math.floor((c.width()/2)-(e.width()/2))});var d=joms.jQuery("#cGallery .photoActions");d.css({width:c.width(),height:0,top:0,left:0});var g=joms.jQuery("#cGallery .photoAction._next");g.css({top:Math.floor((c.height()/2)-(g.height()/2)),right:0});var a=joms.jQuery("#cGallery .photoAction._prev");a.css({top:Math.floor((c.height()/2)-(a.height()/2)),left:0});var b=joms.jQuery("#cGallery .photoTags");b.css({width:c.width(),height:0,top:0,left:0})},displayPhoto:function(b){joms.jQuery("#like-container").html("");var c=joms.jQuery("#cGallery .photoLoad");c.show();joms.gallery.currentPhoto(b);joms.jQuery("#cGallery .photoAction._next img").attr("src",joms.gallery.nextPhoto().thumbnail);joms.jQuery("#cGallery .photoAction._prev img").attr("src",joms.gallery.prevPhoto().thumbnail);joms.gallery.displayPhotoCaption(b.caption);var a=joms.gallery.createPhotoImage(b,function(){var e=joms.jQuery("#cGallery .photoDisplay");var d=joms.jQuery("#cGallery .photoImage");d.fadeOut("fast",function(){e.empty();a.appendTo(e);a.css({top:"3000px",left:"4000px",visibility:"visible",position:"absolute"});var f=a[0].height;var h=a[0].width;if(a[0].height>e.height()){h=h*(e.height()/f);f=e.height()}if(h>e.width()){f=f*(e.width()/h);h=e.width()}var g={width:h,height:f,top:Math.floor((e.height()-f)/2),left:Math.floor((e.width()-h)/2),visibility:"visible",display:"none"};a.data("properties",g).css(g);joms.gallery.switchPhoto(b.id);joms.gallery.displayPhotoTags(b.tags)})});joms.gallery.prefetchPhoto([joms.gallery.prevPhoto(),joms.gallery.nextPhoto()])},createPhotoImage:function(b,c){if(typeof(c)!="function"){c=function(){}}var a=joms.jQuery(new Image()).load(c).attr({id:"photo-"+b.id,"class":"photoImage",alt:b.caption,title:"",src:joms.gallery.getPhotoUrl(b)});return a},prefetchPhoto:function(a){if(!joms.jQuery.isArray(a)){a=[a]}joms.jQuery.each(a,function(c,b){if(!b.loaded){joms.gallery.createPhotoImage(b,function(){b.loaded=true})}})},getPhotoUrl:function(b){var a=joms.jQuery("#cGallery .photoDisplay");var c="";if(b.url.indexOf("option=com_community")==-1){c=b.url}else{c=b.url+"&"+joms.jQuery.param({maxW:a.width(),maxH:a.height()})}return c},displayPhotoHits:function(b){var a=joms.jQuery("#cGallery .photoHitsText");a.text((b!="")?b:jsPlaylist.language.CC_NO_PHOTO_CAPTION_YET)},addPhotoHits:function(){jax.call("community","photos,ajaxAddPhotoHits",joms.gallery.currentPhoto().id)},displayPhotoCaption:function(a){var b=joms.jQuery("#cGallery .photoCaptionText");b.text((a!="")?a:jsPlaylist.language.CC_NO_PHOTO_CAPTION_YET)},editPhotoCaption:function(){var b=joms.jQuery("#cGallery .photoCaption");b.addClass("editMode");var c=joms.jQuery("#cGallery .photoCaptionText");var a=joms.jQuery("#cGallery .photoCaptionInput");a.val(joms.jQuery.trim(c.text()))},cancelPhotoCaption:function(){var b=joms.jQuery("#cGallery .photoCaption");b.removeClass("editMode");var a=joms.jQuery("#cGallery .photoCaptionInput");a.val("")},savePhotoCaption:function(){var d=joms.jQuery("#cGallery .photoCaptionText");var b=joms.jQuery("#cGallery .photoCaptionInput");var c=joms.jQuery.trim(d.text());var a=joms.jQuery.trim(b.val());if(a==""||a==c){joms.gallery.cancelPhotoCaption()}else{jax.call("community","photos,ajaxSaveCaption",joms.gallery.currentPhoto().id,a)}},updatePhotoCaption:function(a,b){var c=joms.jQuery("#cGallery .photoCaptionText");c.text(b);jsPlaylist.photos[joms.gallery.getPlaylistIndex(a)].caption=b;joms.gallery.cancelPhotoCaption()},switchPhoto:function(a){joms.ajax.call("photos,ajaxSwitchPhotoTrigger",[a],{success:function(b){joms.jQuery("#like-container").html(b);joms.jQuery("#cGallery .photoDisplay img").fadeIn("fast");joms.jQuery("#cGallery .photoLoad").hide()}})},displayPhotoWalls:function(a){joms.gallery.switchPhoto(a)},setPhotoAsDefault:function(){if(confirm(jsPlaylist.language.CC_SET_PHOTO_AS_DEFAULT_DIALOG)){jax.call("community","photos,ajaxSetDefaultPhoto",jsPlaylist.album,joms.gallery.currentPhoto().id)}},downloadPhoto:function(){window.open(jsPlaylist.photos[jsPlaylist.currentPlaylistIndex].originalUrl)},updatePhotoReport:function(a){joms.jQuery(".page-action#report-this").remove();joms.jQuery(".page-actions").prepend(a)},updatePhotoBookmarks:function(a){joms.jQuery(".page-action#social-bookmarks").remove();joms.jQuery(".page-actions").append(a)},newPhotoTag:function(b){var a={id:null,userId:null,photoId:null,displayName:null,profileUrl:null,top:null,left:null,width:null,height:null,displayTop:null,displayLeft:null,displayWidth:null,displayHeight:null,"canRemove:":null};joms.jQuery.extend(a,b);return a},createPhotoTag:function(tag){var photo=joms.jQuery("#cGallery .photoImage");var photoTags=joms.jQuery("#cGallery .photoTags");if(typeof(tag)=="string"){tag=eval("("+tag+")")}var singleTag=false;if(!joms.jQuery.isArray(tag)){tag=[tag];singleTag=true}var newPhotoTags=new Array();joms.jQuery.each(tag,function(i,tag){var photoTag=joms.gallery.drawPhotoTag(tag,photo);photoTag.data("tag",tag).attr("id","photoTag-"+tag.id).hover(function(){joms.gallery.showPhotoTag(tag.id,"Label")},function(){joms.gallery.hidePhotoTag(tag.id)}).appendTo(photoTags);var photoTagLabel=joms.jQuery('<div class="photoTagLabel">');photoTagLabel.html(tag.displayName);photoTagLabel.wrapInner("<span></span>").appendTo(photoTag);newPhotoTags.push(photoTag)});if(singleTag){return newPhotoTags[0]}else{return newPhotoTags}},drawPhotoTag:function(b,c){var a=(b.displayWidth!=b.width*c.width());if(a){b.displayWidth=b.width*c.width();b.displayHeight=b.height*c.height();b.displayTop=(b.top*c.height())-(b.displayHeight/2);if(b.displayTop<0){b.displayTop=0}maxTop=c.height()-b.displayHeight;if(b.displayTop>maxTop){b.displayTop=maxTop}b.displayLeft=(b.left*c.width())-(b.displayWidth/2);if(b.displayLeft<0){b.displayLeft=0}maxLeft=c.width()-b.displayWidth;if(b.displayLeft>maxLeft){b.displayLeft=maxLeft}}var d=joms.jQuery('<div class="photoTag">');d.css({width:b.displayWidth,height:b.displayHeight,top:b.displayTop,left:b.displayLeft});var e=joms.jQuery('<div class="photoTagBorder">');e.css({width:b.displayWidth-4,height:b.displayHeight-4,border:"2px solid #222"}).appendTo(d);if(b.id!=null){joms.gallery.updatePlaylistTag(b)}return d},updatePlaylistTag:function(a){var c;var b=jsPlaylist.photos[joms.gallery.getPlaylistIndex(a.photoId)].tags;joms.jQuery.each(b,function(){if(this.id==a.id){c=this}});if(c==undefined){c=b[b.push(joms.gallery.newPhotoTag())-1]}joms.jQuery.extend(c,a)},displayPhotoTags:function(c){joms.gallery.clearPhotoTag();joms.gallery.clearPhotoTextTag();var b=joms.jQuery("#cGallery .photoImage");var a=joms.jQuery("#cGallery .photoTags");a.css({width:b.width(),height:b.height(),top:b.data("properties").top,left:b.data("properties").left});joms.gallery.createPhotoTag(c);joms.gallery.createPhotoTextTag(c)},addPhotoTag:function(b){var a=joms.jQuery("#cGallery .photoTag.new").data("tag");jax.call("community","photos,ajaxAddPhotoTag",a.photoId,b,a.top,a.left,a.width,a.height);joms.gallery.cancelNewPhotoTag()},removePhotoTag:function(a){jax.call("community","photos,ajaxRemovePhotoTag",a.photoId,a.userId);joms.gallery.clearPhotoTag(a);joms.gallery.clearPhotoTextTag(a);var b=jsPlaylist.photos[joms.gallery.getPlaylistIndex(a.photoId)].tags;joms.jQuery.each(b,function(c){if(this.id==a.id){b.splice(c,1)}})},clearPhotoTag:function(a){if(a==undefined){joms.jQuery("#cGallery .photoTag").remove()}else{joms.jQuery("#photoTag-"+a.id).remove()}},showPhotoTag:function(b,a){joms.jQuery("#photoTag-"+b).addClass("show"+a)},hidePhotoTag:function(a){joms.jQuery("#photoTag-"+a).removeClass("show showLabel showForce")},createPhotoTextTag:function(tags){var photoTextTags=joms.jQuery("#cGallery .photoTextTags");if(typeof(tags)=="string"){tags=eval("("+tags+")")}var singleTag=false;if(!joms.jQuery.isArray(tags)){tags=[tags];singleTag=true}var newPhotoTextTags=new Array();joms.jQuery.each(tags,function(i,tag){if(tag.id==undefined){return}var photoTextTag=joms.jQuery('<span class="photoTextTag"></span>');photoTextTag.data("tag",tag).attr("id","photoTextTag-"+tag.id).hover(function(){joms.gallery.showPhotoTag(tag.id,"Force")},function(){joms.gallery.hidePhotoTag(tag.id)}).appendTo(photoTextTags);var photoTextTagLink=joms.jQuery("<a>");photoTextTagLink.attr("href",tag.profileUrl).html(tag.displayName).prependTo(photoTextTag);if(tag.canRemove){var photoTextTagActions=joms.jQuery('<span class="photoTextTagActions"></span>');photoTextTagActions.appendTo(photoTextTag);var photoTextTagAction_remove=joms.jQuery('<a class="photoTextTagAction" href="javascript: void(0);"></a>');photoTextTagAction_remove.addClass("_remove").html(jsPlaylist.language.CC_REMOVE).click(function(){joms.gallery.removePhotoTag(tag)}).appendTo(photoTextTagActions);photoTextTagActions.before(" ").prepend("(").append(")")}newPhotoTextTags.push(photoTextTag)});joms.gallery.commifyTextTags();return newPhotoTextTags},commifyTextTags:function(){joms.jQuery("#cGallery .photoTextTags .comma").remove();photoTextTag=joms.jQuery("#cGallery .photoTextTag");photoTextTag.each(function(b){if(b==0){return}var a=joms.jQuery('<span class="comma"></span>');a.html(", ").prependTo(this)})},clearPhotoTextTag:function(a){if(a==undefined){joms.jQuery("#cGallery .photoTextTag").remove()}else{joms.jQuery("#photoTextTag-"+a.id).remove();joms.gallery.commifyTextTags()}},startTagMode:function(){joms.jQuery("#cGallery .photoTagInstructions").slideDown("fast");joms.jQuery("#startTagMode").hide();var d=joms.jQuery("#cGallery .photoViewport");d.addClass("tagMode");var c=joms.jQuery("#cGallery .photoImage");var b=c;var a=joms.jQuery("#cGallery .photoTags");a.click(function(h){joms.jQuery(".photoTag.new").remove();var g=joms.gallery.createPhotoTag(joms.gallery.newPhotoTag({photoId:joms.gallery.currentPhoto().id,top:(h.pageY-joms.jQuery(this).offset().top)/c.height(),left:(h.pageX-joms.jQuery(this).offset().left)/c.width(),width:jsPlaylist.config.defaultTagWidth/c.width(),height:jsPlaylist.config.defaultTagHeight/c.height()}));g.addClass("new");var f=joms.jQuery("#cGallery .photoTagActions");f.css({top:g.position().top+g.outerHeight(true),left:g.position().left}).show();f.children("*").click(function(e){e.stopPropagation()})})},stopTagMode:function(){joms.gallery.cancelNewPhotoTag();var b=joms.jQuery("#cGallery .photoViewport");b.removeClass("tagMode");var a=joms.jQuery("#cGallery .photoTags");a.unbind("click");joms.jQuery("#cGallery .photoTagInstructions").hide();joms.jQuery("#startTagMode").show();cWindowHide()},selectNewPhotoTagFriend:function(){var a=joms.jQuery("#cGallery .photoTagFriend");if(a.length<1){cWindowShow(function(){joms.jQuery("#cWindowContent").html(jsPlaylist.language.CC_PHOTO_TAG_NO_FRIEND)},jsPlaylist.language.CC_SELECT_FRIEND,450,80);return}if(joms.gallery.currentPhoto().tags.length==a.length){cWindowShow(function(){joms.jQuery("#cWindowContent").html(jsPlaylist.language.CC_PHOTO_TAG_ALL_TAGGED)},jsPlaylist.language.CC_SELECT_FRIEND,450,80);return}cWindowShow(function(){joms.gallery.showPhotoTagFriends()},jsPlaylist.language.CC_SELECT_FRIEND,300,300);cWindowActions('<button class="button" onclick="joms.gallery.confirmPhotoTagFriend();">'+jsPlaylist.language.CC_CONFIRM+"</button>")},confirmPhotoTagFriend:function(){joms.jQuery("#cWindow .js-system-message").hide();var a=joms.jQuery("#cWindow .photoTagFriend input:checked");if(a.length>0){joms.gallery.addPhotoTag(a.val())}else{joms.jQuery("#cWindow .js-system-message").show();joms.jQuery("#cWindow .js-system-message").fadeOut(5000)}},showPhotoTagFriends:function(){joms.jQuery("#cWindowContent").empty();joms.jQuery("#cGallery .photoTagSelectFriend").clone().appendTo("#cWindowContent");var a=new Array();joms.jQuery.each(joms.gallery.currentPhoto().tags,function(){a.push(this.userId)});joms.gallery.filterPhotoTagFriend(a);setTimeout("joms.jQuery('#cWindowContent .photoTagFriendFilter').focus()",300)},filterPhotoTagFriend:function(b){var a=joms.jQuery("#cWindow .photoTagFriend");var c=joms.jQuery("#cWindow .photoTagFriendFilter");var d=joms.jQuery.trim(c.val());if(b!=undefined){joms.jQuery.each(b,function(f,e){a.filter(function(){return joms.jQuery(this).attr("id")=="photoTagFriend-"+e}).addClass("tagged")});return}if(d==""){a.not(".tagged").removeClass("hide");return}a.not(".tagged").addClass("hide").filter(function(){return(this.textContent||this.innerText||"").toUpperCase().indexOf(d.toUpperCase())>=0}).removeClass("hide")},cancelNewPhotoTag:function(){var a=joms.jQuery(".photoTag.new");a.remove();var b=joms.jQuery("#cGallery .photoTagActions");b.hide()},displayCreator:function(a){jax.call("community","photos,ajaxDisplayCreator",a)},setProfilePicture:function(){var a="jax.call('community', 'photos,ajaxLinkToProfile', '"+joms.gallery.currentPhoto().id+"');";cWindowShow(a,"",450,100)},rotatePhoto:function(b){var a=joms.gallery.currentPhoto().id;joms.ajax.call("photos,ajaxRotatePhoto",[a,b],{success:function(c,e,f){var d=jsPlaylist.photos[joms.gallery.getPlaylistIndex(c)];d.url=e;d.thumbnail=f;joms.gallery.displayPhoto(d)}})}}});function getPlaylistIndex(a){joms.gallery.getPlaylistIndex(a)}function nextPhoto(a){joms.gallery.nextPhoto(a)}function prevPhoto(a){joms.gallery.prevPhoto(a)}function currentPhoto(a){joms.gallery.currentPhoto(a)}function urlPhotoId(a){joms.gallery.urlPhotoId(a)}function initGallery(){joms.gallery.init()}function displayViewport(){joms.gallery.displayViewPort()}function displayPhoto(a){joms.gallery.displayPhoto(a)}function createPhotoImage(a,b){joms.gallery.createPhotoImage(a,b)}function prefetchPhoto(a){joms.gallery.prefetchPhoto(a)}function getPhotoUrl(a){joms.gallery.getPhotoUrl(a)}function displayPhotoCaption(a){joms.gallery.displayPhotoCaption(a)}function editPhotoCaption(){joms.gallery.editPhotoCaption()}function cancelPhotoCaption(){joms.gallery.cancelPhotoCaption()}function savePhotoCaption(){joms.gallery.savePhotoCaption()}function updatePhotoCaption(a,b){joms.gallery.updatePhotoCaption(a,b)}function displayPhotoWalls(a){joms.gallery.displayPhotoWalls(a)}function setPhotoAsDefault(){joms.gallery.setPhotoAsDefault()}function removePhoto(){joms.gallery.confirmRemovePhoto()}function downloadPhoto(){joms.gallery.downloadPhoto()}function updatePhotoReport(a){joms.gallery.updatePhotoReport(a)}function newPhotoTag(a){joms.gallery.newPhotoTag(a)}function createPhotoTag(a){joms.gallery.createPhotoTag(a)}function drawPhotoTag(a,b){joms.gallery.drawPhotoTag(a,b)}function updatePlaylistTag(a){joms.gallery.updatePlaylistTag(a)}function displayPhotoTags(a){joms.gallery.displayPhotoTags(a)}function addPhotoTag(a){joms.gallery.addPhotoTag(a)}function removePhotoTag(a){joms.gallery.removePhotoTag(a)}function clearPhotoTag(a){joms.gallery.clearPhotoTag(a)}function showPhotoTag(b,a){joms.gallery.showPhotoTag(b,a)}function hidePhotoTag(a){joms.gallery.hidePhotoTag(a)}function createPhotoTextTag(a){joms.gallery.createPhotoTextTag(a)}function commifyTextTags(){joms.gallery.commifyTextTags()}function clearPhotoTextTag(a){joms.gallery.clearPhotoTextTag(a)}function startTagMode(){joms.gallery.startTagMode()}function stopTagMode(){joms.gallery.stopTagMode()}function selectNewPhotoTagFriend(){joms.gallery.selectNewPhotoTagFriend()}function confirmPhotoTagFriend(){joms.gallery.confirmPhotoTagFriend()}function showPhotoTagFriends(){joms.gallery.showPhotoTagFriends()}function filterPhotoTagFriend(a){joms.gallery.filterPhotoTagFriend(a)}function cancelNewPhotoTag(){joms.gallery.cancelNewPhotoTag()}function displayCreator(a){joms.gallery.displayCreator(a)}function setProfilePicture(){joms.gallery.setProfilePicture()};