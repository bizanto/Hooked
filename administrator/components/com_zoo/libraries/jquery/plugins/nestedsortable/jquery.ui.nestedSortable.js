(function(a){a.widget("ui.nestedSortable",a.extend({},a.ui.sortable.prototype,{options:{tabSize:20,disableNesting:"ui-nestedSortable-no-nesting",errorClass:"ui-nestedSortable-error",listType:"ol"},_create:function(){this.element.data("sortable",this.element.data("sortableTree"));return a.ui.sortable.prototype._create.apply(this,arguments)},_mouseDrag:function(b){this.position=this._generatePosition(b);this.positionAbs=this._convertPositionTo("absolute");if(!this.lastPositionAbs)this.lastPositionAbs=
this.positionAbs;if(this.options.scroll){var c=this.options,d=false;if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML"){if(this.overflowOffset.top+this.scrollParent[0].offsetHeight-b.pageY<c.scrollSensitivity)this.scrollParent[0].scrollTop=d=this.scrollParent[0].scrollTop+c.scrollSpeed;else if(b.pageY-this.overflowOffset.top<c.scrollSensitivity)this.scrollParent[0].scrollTop=d=this.scrollParent[0].scrollTop-c.scrollSpeed;if(this.overflowOffset.left+this.scrollParent[0].offsetWidth-
b.pageX<c.scrollSensitivity)this.scrollParent[0].scrollLeft=d=this.scrollParent[0].scrollLeft+c.scrollSpeed;else if(b.pageX-this.overflowOffset.left<c.scrollSensitivity)this.scrollParent[0].scrollLeft=d=this.scrollParent[0].scrollLeft-c.scrollSpeed}else{if(b.pageY-a(document).scrollTop()<c.scrollSensitivity)d=a(document).scrollTop(a(document).scrollTop()-c.scrollSpeed);else if(a(window).height()-(b.pageY-a(document).scrollTop())<c.scrollSensitivity)d=a(document).scrollTop(a(document).scrollTop()+
c.scrollSpeed);if(b.pageX-a(document).scrollLeft()<c.scrollSensitivity)d=a(document).scrollLeft(a(document).scrollLeft()-c.scrollSpeed);else if(a(window).width()-(b.pageX-a(document).scrollLeft())<c.scrollSensitivity)d=a(document).scrollLeft(a(document).scrollLeft()+c.scrollSpeed)}d!==false&&a.ui.ddmanager&&!c.dropBehaviour&&a.ui.ddmanager.prepareOffsets(this,b)}this.positionAbs=this._convertPositionTo("absolute");if(!this.options.axis||this.options.axis!="y")this.helper[0].style.left=this.position.left+
"px";if(!this.options.axis||this.options.axis!="x")this.helper[0].style.top=this.position.top+"px";for(d=this.items.length-1;d>=0;d--){var e=this.items[d],f=e.item[0],g=this._intersectsWithPointer(e);if(g)if(f!=this.currentItem[0]&&this.placeholder[g==1?"next":"prev"]()[0]!=f&&!a.ui.contains(this.placeholder[0],f)&&(this.options.type=="semi-dynamic"?!a.ui.contains(this.element[0],f):true)){this.direction=g==1?"down":"up";if(this.options.tolerance=="pointer"||this._intersectsWithSides(e))this._rearrange(b,
e);else break;this._clearEmpty(f);this._trigger("change",b,this._uiHash());break}}for(itemBefore=this.placeholder[0].previousSibling;itemBefore!=null;)if(itemBefore.nodeType==1&&itemBefore!=this.currentItem[0])break;else itemBefore=itemBefore.previousSibling;parentItem=this.placeholder[0].parentNode.parentNode;newList=document.createElement(c.listType);if(parentItem!=null&&parentItem.nodeName=="LI"&&this.positionAbs.left<a(parentItem).offset().left){a(parentItem).after(this.placeholder[0]);this._clearEmpty(parentItem)}else if(itemBefore!=
null&&itemBefore.nodeName=="LI"&&this.positionAbs.left>a(itemBefore).offset().left+this.options.tabSize)if(a(itemBefore).hasClass(this.options.disableNesting))a(this.placeholder[0]).addClass(this.options.errorClass).css("marginLeft",this.options.tabSize);else{a(this.placeholder[0]).hasClass(this.options.errorClass)&&a(this.placeholder[0]).css("marginLeft",0).removeClass(this.options.errorClass);itemBefore.children[1]==null&&itemBefore.appendChild(newList);itemBefore.children[1].appendChild(this.placeholder[0])}else if(itemBefore!=
null){a(this.placeholder[0]).hasClass(this.options.errorClass)&&a(this.placeholder[0]).css("marginLeft",0).removeClass(this.options.errorClass);a(itemBefore).after(this.placeholder[0])}else a(this.placeholder[0]).hasClass(this.options.errorClass)&&a(this.placeholder[0]).css("marginLeft",0).removeClass(this.options.errorClass);this._contactContainers(b);a.ui.ddmanager&&a.ui.ddmanager.drag(this,b);this._trigger("sort",b,this._uiHash());this.lastPositionAbs=this.positionAbs;return false},serialize:function(b){var c=
this._getItemsAsjQuery(b&&b.connected),d=[];b=b||{};a(c).each(function(){var e=(a(b.item||this).attr(b.attribute||"id")||"").match(b.expression||/(.+)[-=_](.+)/),f=(a(b.item||this).parent(b.listType).parent("li").attr(b.attribute||"id")||"").match(b.expression||/(.+)[-=_](.+)/);if(e)d.push((b.key||e[1]+"["+(b.key&&b.expression?e[1]:e[2])+"]")+"="+(f?b.key&&b.expression?f[1]:f[2]:"root"))});!d.length&&b.key&&d.push(b.key+"=");return d.join("&")},toArray:function(b){function c(g,h,i){right=i+1;if(a(g).children(b.listType).children("li").length>
0){h++;a(g).children(b.listType).children("li").each(function(){right=c(a(this),h,right)});h--}id=a(g).attr("id").match(b.expression||/(.+)[-=_](.+)/);if(h===d+1)pid="root";else{parentItem=a(g).parent(b.listType).parent("li").attr("id").match(b.expression||/(.+)[-=_](.+)/);pid=parentItem[2]}e.push({item_id:id[2],parent_id:pid,depth:h,left:i,right:right});return i=right+1}b=b||{};var d=b.startDepthCount||0,e=[],f=2;e.push({item_id:"root",parent_id:"none",depth:d,left:"1",right:(a("li",this.element).length+
1)*2});a(this.element).children("li").each(function(){f=c(a(this),d+1,f)});return e},_createPlaceholder:function(b){var c=b||this,d=c.options;if(!d.placeholder||d.placeholder.constructor==String){var e=d.placeholder;d.placeholder={element:function(){var f=a(document.createElement(c.currentItem[0].nodeName)).addClass(e||c.currentItem[0].className+" ui-sortable-placeholder").removeClass("ui-sortable-helper")[0];if(!e)f.style.visibility="hidden";return f},update:function(f,g){if(!(e&&!d.forcePlaceholderSize)){if(!g.height()||
g.css("height")=="auto")g.height(c.currentItem.height());g.width()||g.width(c.currentItem.width())}}}}c.placeholder=a(d.placeholder.element.call(c.element,c.currentItem));c.currentItem.after(c.placeholder);d.placeholder.update(c,c.placeholder)},_clear:function(){a.ui.sortable.prototype._clear.apply(this,arguments);for(var b=this.items.length-1;b>=0;b--)this._clearEmpty(this.items[b].item[0]);return true},_clearEmpty:function(b){b.children[1]&&b.children[1].children.length==0&&b.removeChild(b.children[1])}}));
a.ui.nestedSortable.prototype.options=a.extend({},a.ui.sortable.prototype.options,a.ui.nestedSortable.prototype.options)})(jQuery);