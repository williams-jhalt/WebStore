/*!
 * jScroll - jQuery Plugin for Infinite Scrolling / Auto-Paging
 * http://jscroll.com/
 *
 * Copyright 2011-2013, Philip Klauzinski
 * http://klauzinski.com/
 * Dual licensed under the MIT and GPL Version 2 licenses.
 * http://jscroll.com/#license
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @author Philip Klauzinski
 * @version 2.3.4
 * @requires jQuery v1.4.3+
 * @preserve
 */
(function(e){'use strict';e.jscroll={defaults:{debug:!1,autoTrigger:!0,autoTriggerUntil:!1,loadingHtml:'<small>Loading...</small>',padding:0,nextSelector:'a:last',contentSelector:'',pagingSelector:'',callback:!1}};var t=function(t,r){var f=t.data('jscroll'),y=(typeof r==='function')?{callback:r}:r,n=e.extend({},e.jscroll.defaults,y,f||{}),u=(t.css('overflow-y')==='visible'),v=t.find(n.nextSelector).first(),g=e(window),m=e('body'),l=u?g:t,w=e.trim(v.attr('href')+' '+n.contentSelector),j=function(){var t=e(n.loadingHtml).filter('img').attr('src');if(t){var l=new Image();l.src=t}},a=function(){if(!t.find('.jscroll-inner').length){t.contents().wrapAll('<div class="jscroll-inner" />')}},s=function(e){var t;if(n.pagingSelector){e.closest(n.pagingSelector).hide()}
else{t=e.parent().not('.jscroll-inner,.jscroll-added').addClass('jscroll-next-parent').hide();if(!t.length){e.wrap('<div class="jscroll-next-parent" />').parent().hide()}}},o=function(){return l.unbind('.jscroll').removeData('jscroll').find('.jscroll-inner').children().unwrap().filter('.jscroll-added').children().unwrap()},p=function(){a();var e=t.find('div.jscroll-inner').first(),g=t.data('jscroll'),r=parseInt(t.css('borderTopWidth'),10),f=isNaN(r)?0:r,p=parseInt(t.css('paddingTop'),10)+f,s=u?l.scrollTop():t.offset().top,c=e.length?e.offset().top:0,o=Math.ceil(s-c+l.height()+p);if(!g.waiting&&o+n.padding>=e.outerHeight()){i('info','jScroll:',e.outerHeight()-o,'from bottom. Loading next request...');return d()}},h=function(e){e=e||t.data('jscroll');if(!e||!e.nextHref){i('warn','jScroll: nextSelector not found - destroying');o();return!1}
else{c();return!0}},c=function(){var e=t.find(n.nextSelector).first();if(!e.length){return};if(n.autoTrigger&&(n.autoTriggerUntil===!1||n.autoTriggerUntil>0)){s(e);if(m.height()<=g.height()){p()};l.unbind('.jscroll').bind('scroll.jscroll',function(){return p()});if(n.autoTriggerUntil>0){n.autoTriggerUntil--}}
else{l.unbind('.jscroll');e.bind('click.jscroll',function(){s(e);d();return!1})}},d=function(){var r=t.find('div.jscroll-inner').first(),l=t.data('jscroll');l.waiting=!0;r.append('<div class="jscroll-added" />').children('.jscroll-added').last().html('<div class="jscroll-loading">'+n.loadingHtml+'</div>');return t.animate({scrollTop:r.outerHeight()},0,function(){r.find('div.jscroll-added').last().load(l.nextHref,function(r,s){if(s==='error'){return o()};var c=e(this).find(n.nextSelector).first();l.waiting=!1;l.nextHref=c.attr('href')?e.trim(c.attr('href')+' '+n.contentSelector):!1;e('.jscroll-next-parent',t).remove();h();if(n.callback){n.callback.call(this)};i('dir',l)})})},i=function(e){if(n.debug&&typeof console==='object'&&(typeof e==='object'||typeof console[e]==='function')){if(typeof e==='object'){var l=[];for(var t in e){if(typeof console[t]==='function'){l=(e[t].length)?e[t]:[e[t]];console[t].apply(console,l)}
else{console.log.apply(console,l)}}}
else{console[e].apply(console,Array.prototype.slice.call(arguments,1))}}};t.data('jscroll',e.extend({},f,{initialized:!0,waiting:!1,nextHref:w}));a();j();c();e.extend(t.jscroll,{destroy:o});return t};e.fn.jscroll=function(n){return this.each(function(){var l=e(this),r=l.data('jscroll'),i;if(r&&r.initialized){return};i=new t(l,n)})}})(jQuery);