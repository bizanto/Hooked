/* Copyright (C) 2007 - 2011 YOOtheme GmbH, YOOtheme Proprietary Use License (http://www.yootheme.com/license) */

var Slimbox;
(function(){function p(){q.setStyles({top:window.getScrollTop(),height:window.getHeight()})}function i(a){["object",window.ie?"select":"embed"].forEach(function(k){$each(document.getElementsByTagName(k),function(j){if(a)z[j]=j.style.visibility;j.style.visibility=a?"hidden":z[j]})});q.style.display=a?"":"none";var c=a?"addEvent":"removeEvent";window[c]("scroll",p)[c]("resize",p);document[c]("keydown",A)}function H(a){switch(a.code){case 27:case 88:case 67:B();break;case 37:case 80:C();break;case 39:case 78:D()}a.preventDefault()}
function C(){return w(r)}function D(){return w(l)}function w(a){if(h==1&&a>=0){h=2;m=a;r=(m||!b.loop?m:e.length)-1;l=m+1;if(l==e.length)l=b.loop?0:-1;$$(s,t,f,n).setStyle("display","none");d.bottom.stop().set(0);d.image.set(0);g.className="lbLoading";o=new Image;o.onload=x;o.src=e[a][0]}return false}function x(){switch(h++){case 2:g.className="";f.setStyles({backgroundImage:"url("+e[m][0]+")",display:""});$$(f,u).setStyle("width",o.width);$$(f,s,t).setStyle("height",o.height);E.setHTML(e[m][1]||"");
F.setHTML(b.showCounter&&e.length>1?b.counterText.replace(/{x}/,m+1).replace(/{y}/,e.length):"");if(r>=0)I.src=e[r][0];if(l>=0)J.src=e[l][0];if(g.clientHeight!=f.offsetHeight){d.resize.start({height:f.offsetHeight});break}h++;case 3:if(g.clientWidth!=f.offsetWidth){d.resize.start({width:f.offsetWidth,marginLeft:-f.offsetWidth/2});break}h++;case 4:n.setStyles({top:y+g.clientHeight,marginLeft:g.style.marginLeft,visibility:"hidden",display:""});d.image.start(1);break;case 5:if(r>=0)s.style.display="";
if(l>=0)t.style.display="";b.animateCaption&&d.bottom.set(-u.offsetHeight).start(0);n.style.visibility="";h=1}}function B(){if(h){h=0;o.onload=Class.empty;for(var a in d)d[a].stop();$$(g,n).setStyle("display","none");d.overlay.chain(i).start(0)}return false}var z={},h=0,b,e,m,r,l,y,A,d,o,I=new Image,J=new Image,q,g,f,s,t,n,u,E,F;window.addEvent("domready",function(){A=H.bindWithEvent();$(document.body).adopt($$([q=new Element("div",{id:"lbOverlay"}),g=new Element("div",{id:"lbCenter"}),n=new Element("div",
{id:"lbBottomContainer"})]).setStyle("display","none"));f=(new Element("div",{id:"lbImage"})).injectInside(g).adopt(s=new Element("a",{id:"lbPrevLink",href:"#"}),t=new Element("a",{id:"lbNextLink",href:"#"}));s.onclick=C;t.onclick=D;var a;u=(new Element("div",{id:"lbBottom"})).injectInside(n).adopt(a=new Element("a",{id:"lbCloseLink",href:"#"}),E=new Element("div",{id:"lbCaption"}),F=new Element("div",{id:"lbNumber"}),new Element("div",{styles:{clear:"both"}}));a.onclick=q.onclick=B;d={overlay:q.effect("opacity",
{duration:500}).set(0),image:f.effect("opacity",{duration:500,onComplete:x}),bottom:u.effect("margin-top",{duration:400})}});Slimbox={open:function(a,c,k){b=$extend({loop:false,overlayOpacity:0.8,resizeDuration:400,resizeTransition:false,initialWidth:250,initialHeight:250,animateCaption:true,showCounter:true,counterText:"Image {x} of {y}"},k||{});if(typeof a=="string"){a=[[a,c]];c=0}e=a;b.loop=b.loop&&e.length>1;p();i(true);y=window.getScrollTop()+window.getHeight()/15;d.resize=g.effects($extend({duration:b.resizeDuration,
onComplete:x},b.resizeTransition?{transition:b.resizeTransition}:{}));g.setStyles({top:y,width:b.initialWidth,height:b.initialHeight,marginLeft:-(b.initialWidth/2),display:""});d.overlay.start(b.overlayOpacity);h=1;return w(c)}};Element.extend({slimbox:function(a,c){$$(this).slimbox(a,c);return this}});Elements.extend({slimbox:function(a,c,k){c=c||function(v){return[v.href,v.title]};k=k||function(){return true};var j=this;j.forEach(function(v){v.onclick=function(){var G=j.filter(k,this);return Slimbox.open(G.map(c),
G.indexOf(this),a)}});return j}})})();Slimbox.scanPage=function(){var p=$$("a").filter(function(i){return i.rel&&i.rel.test(/^lightbox/i)});$$(p).slimbox({},null,function(i){return this==i||this.rel.length>8&&this.rel==i.rel})};window.addEvent("domready",Slimbox.scanPage);