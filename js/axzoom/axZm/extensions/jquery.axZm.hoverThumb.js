/**
* Plugin: jQuery AJAX-ZOOM, jquery.axZm.hoverThumb.js
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Extension Version: 1.4
* Extension Date: 2016-03-07
* URL: http://www.ajax-zoom.com
* Demo: http://www.ajax-zoom.com/examples/example6.php
*/

;(function($){
 	/*
	* jQuery Browser Plugin v0.0.6
	* https://github.com/gabceb/jquery-browser-plugin
	* Original jquery-browser code Copyright 2005, 2013 jQuery Foundation, Inc. and other contributors
	* http://jquery.org/license
	* Modifications Copyright 2013 Gabriel Cebrian
	* https://github.com/gabceb
	* Released under the MIT license
	* Date: 2013-07-29T17:23:27-07:00
	*/
	
	var axZmBrowserMigrate = function(){    	
	    var matched, 
        	brsr;
        	
	    var uaMatch = function( ua ) {
	        ua = ua.toLowerCase();
	        
	        var match = /(opr)[\/]([\w.]+)/.exec( ua ) ||
	        /(edge)[ \/]([\w.]+)/.exec( ua ) || 
	        /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
	        /(version)[ \/]([\w.]+).*(safari)[ \/]([\w.]+)/.exec( ua ) ||
	        /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
	        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
	        /(msie) ([\w.]+)/.exec( ua ) || 
	        
	        ua.indexOf('trident') >= 0 && /(rv)(?::| )([\w.]+)/.exec( ua ) ||
	        ua.indexOf('compatible') < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
	        [];
	        
	        var platform_match = /(ipad)/.exec( ua ) ||
	        /(iphone)/.exec( ua ) ||
	        /(android)/.exec( ua ) ||
	        /(windows phone)/.exec( ua ) ||
	        /(win)/.exec( ua ) ||
	        /(mac)/.exec( ua ) ||
	        /(linux)/.exec( ua ) ||
	        /(cros)/i.exec( ua ) ||
	        [];
	     
	        return {
	            browser: match[3] || match[1] || '',
	            version: match[2] || '0',
	            platform: platform_match[0] || ''
	        };
	    };
	    
	    matched = uaMatch(window.navigator.userAgent);
	    brsr = {};
	    
	    if (matched.browser){
	        brsr[matched.browser] = true;
	        brsr.version = matched.version;
	        brsr.versionNumber = parseInt(matched.version);
	    }
	    
	    if (matched.platform) {
	        brsr[matched.platform] = true;
	    }
	    
	    // These are all considered mobile platforms, meaning they run a mobile browser
	    if ( brsr.android || brsr.ipad || brsr.iphone || brsr[ "windows phone" ] ) {
	        brsr.mobile = true;
	    }
	    
	    // These are all considered desktop platforms, meaning they run a desktop browser
	    if ( brsr.cros || brsr.mac || brsr.linux || brsr.win ) {
	        brsr.desktop = true;
	    }
	    
	    // Chrome, Opera 15+ and Safari are webkit based browsers
	    if ( brsr.chrome || brsr.opr || brsr.safari ) {
	        brsr.webkit = true;
	    }
	    
	    // IE11 has a new token so we will assign it msie to avoid breaking changes
	    if ( brsr.rv || brsr.edge){
	        matched.browser = 'msie';
	        brsr.msie = true;
	    }
	    
	    if (brsr.edge){
    		brsr.edge = true;
		}
	            
	    // Opera 15+ are identified as opr
	    if ( brsr.opr ){
	        matched.browser = 'opera';
	        brsr.opera = true;
	    }
	    
	    // Stock Android browsers are marked as Safari on Android.
	    if ( brsr.safari && brsr.android ){
	        matched.browser = 'android';
	        brsr.android = true;
	    }
	    
	    // Assign the name and platform variable
	    brsr.name = matched.browser;
	    brsr.platform = matched.platform;
	    
	    if (brsr.msie && navigator.userAgent.indexOf('Trident/5.0') != -1){
        	brsr.versionNumber = 9;
		}
	    
	    // IE 11
		if (brsr.mozilla && brsr.msie){
			delete brsr.mozilla;
		}
		
		// EDGE
		if (brsr.chrome && brsr.msie){
			delete brsr.chrome;
		}
	            
		return brsr;	
	};
	var browser = axZmBrowserMigrate();
	
	/*!
	* Plugin: jQuery AJAX-ZOOM, jquery.axZm.hoverThumb.js
	* Copyright: Copyright (c) Vadim Jacobi
	*/
	$.fn.extend({azHoverThumb:function(F){var G={instantWrapClass:null,zoomRatio:1.5,fadeInSpeed:250,zoomEsingIn:"swing",zoomEsingOut:"swing",zoomInSpeed:250,zoomOutSpeed:100,parentHeightRatio:!1,maxHeight:1,parentWidthRatio:!1,overlay:!0,overlayColor:"#000",overlayOpacity:.4,axZmPath:"auto",ajaxZoomOpenMode:"fullscreen",exampleFullscreen:"mouseOverExtension",exampleFancyboxFullscreen:"mouseOverExtension",exampleFancybox:"modal",exampleColorbox:"modal",exampleIframe:"mouseOverExtension",iframeLink:"example33_vario.php",
	iframeClose:"Close zoom",iframeParam:"noSplash=1&stepZoom=1&mNavi=mZoomOut:5,mZoomIn:15",axZmCallBacks:{},fullScreenApi:!1,fancyBoxParam:{boxMargin:0,boxPadding:10,boxCenterOnScroll:!0,boxOverlayShow:!0,boxOverlayOpacity:.75,boxOverlayColor:"#777",boxTransitionIn:"fade",boxTransitionOut:"fade",boxSpeedIn:300,boxSpeedOut:300,boxEasingIn:"swing",boxEasingOut:"swing",boxShowCloseButton:!0,boxEnableEscapeButton:!0,boxOnComplete:function(){},boxTitleShow:!0,boxTitlePosition:"float",boxTitleFormat:null},
	colorBoxParam:{transition:"elastic",speed:300,scrolling:!0,title:!0,opacity:.9,className:!1,current:"image {current} of {total}",previous:"previous",next:"next",close:"close",onOpen:!1,onLoad:!1,onComplete:!1,onClosed:!1,overlayClose:!0,escKey:!0}};return this.each(function(){var c=$(this),g,k,y,f,z,D,p,q,A,a=$.extend(!0,{},G,F),B,m,r,h,t,u,v,w,x;$.isNumeric(a.zoomRatio)?a.zoomRatio=Math.abs(parseFloat(a.zoomRatio)):a.zoomRatio=1;a.instantWrapClass&&c.wrap('<div class="'+a.instantWrapClass+'"></div>');
	if(c.parent().is(".axZmHoverThumbWrap"))a.destroy&&c.data("ieTimer")&&clearInterval(c.data("ieTimer"));else{c.wrap('<div class="azHoverThumbWrap"></div>');g=c.parent();f=g.parent();if(!a.axZmPath||"auto"==a.axZmPath)if($.isFunction($.fn.axZm))a.axZmPath=$.fn.axZm.installPath();else{alert("/axZm/jquery.axZm.js is not loaded");return}"/"!=a.axZmPath.slice(-1)&&(a.axZmPath+="/");var I=function(e){var b=c.attr("data-group"),d=c.attr("data-img"),l=c.attr("data-descr"),g=[],C={},h=f.data("ajaxZoomOpenMode")||
	a.ajaxZoomOpenMode;if(d)b?$('img[data-group="'+b+'"]').each(function(){var a=$(this).attr("data-img");a&&(g.push(a),C[a.split("/").reverse()[0]]=$(this).attr("data-descr"))}):(g.push(d),C[d.split("/").reverse()[0]]=l),H(e,{zoomData:g.join("|"),zoomFile:d,zoomDescr:C,ajaxZoomOpenMode:h,fullScreenApi:f.data("fullScreenApi")||a.fullScreenApi});else return!1},J=function(e){k.remove();k=null;g.css("display","block");a.iframeClose&&(y.remove(),y=null);n()},H=function(e,b){var d=function(){};if("iframe"==
	b.ajaxZoomOpenMode)d="?zoomData="+b.zoomFile,a.exampleIframe&&(d+="&example="+a.exampleIframe),a.iframeParam&&(d="&"==a.iframeParam.substring(0,1)?d+a.iframeParam:d+("&"+a.iframeParam)),g.css("display","none"),k=$('<iframe src="'+a.iframeLink+d+'" frameborder="0" class="azHoverThumbWrap" allowfullscreen>').appendTo(f),a.iframeClose&&(y=$('<div class="azHoverIframeClose">'+("string"==$.type(a.iframeClose)?a.iframeClose:"")+"</div>").bind("click",J).appendTo(f));else if("fullscreen"==b.ajaxZoomOpenMode){var c=
	$.extend(!0,{},a.axZmCallBacks);$.fn.axZm.openFullScreen(e,a.axZmPath,"zoomFile="+b.zoomFile+(b.zoomData?"&zoomData="+b.zoomData:"&zoomDir="+b.zoomDir)+"&example="+a.exampleFullscreen,c,"window",b.fullScreenApi,!1)}else if("fancyboxFullscreen"==b.ajaxZoomOpenMode){if(!$.isFunction($.openAjaxZoomInFancyBox))return alert("Please include following scripts in the head section:\n\n/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.css \n/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.pack.js \n/axZm/extensions/jquery.axZm.openAjaxZoomInFancyBox.js \n\nImportant: it has to be adjusted Fancybox from AJAX-ZOOM package!\n"),
	!1;30>a.fancyBoxParam.boxMargin&&(a.fancyBoxParam.boxMargin=30);c=$.extend(!0,{},a.axZmCallBacks);$.openAjaxZoomInFancyBox($.extend(!0,{},{axZmPath:a.axZmPath,queryString:"example="+a.exampleFancyboxFullscreen+"&zoomFile="+b.zoomFile+(b.zoomData?"&zoomData="+b.zoomData:"&zoomDir="+b.zoomDir),fullScreenApi:b.fullScreenApi,ajaxZoomCallbacks:c,boxOnClosed:d},c))}else if("fancybox"==b.ajaxZoomOpenMode){$("#axZmTempBody, #axZmWrap").axZmRemove(!0);var h=$("<div />").css({display:"none"}).attr("id","axZmWrap").appendTo("body"),
	c=$.fn.axZm.mergeCallBackObj({onStart:function(){h.css("display","");var c={showNavArrows:!1,enableKeyboardNav:!1,hideOnContentClick:!1,scrolling:"no",width:"auto",height:"auto",autoScale:!1,autoDimensions:!0,href:"#axZmWrap",title:b.zoomDescr[b.zoomFile.split("/").reverse()[0]]||" ",onClosed:function(){$.fn.axZm.spinStop();$.fn.axZm.remove();$("#axZmTempBody").axZmRemove(!0);$("#axZmTempLoading").axZmRemove(!0);$("#axZmWrap").axZmRemove(!0)},onComplete:function(b,c,d){" "==d.title&&("float"!=a.fancyBoxParam.boxTitlePosition&&
	"over"!=a.fancyBoxParam.boxTitlePosition||$("#fancybox-title").hide())},beforeClose:function(){$.fn.axZm.spinStop();$.fn.axZm.remove();$("#axZmTempBody").axZmRemove(!0);$("#axZmTempLoading").axZmRemove(!0);$("#axZmWrap").axZmRemove(!0)}},d={};$.each(a.fancyBoxParam,function(a,b){a=a.substr(3);d[a.charAt(0).toLowerCase()+a.slice(1)]=b});$.fancybox($.extend(!0,{},d,c))},onImageChange:function(){if(a.fancyBoxParam.boxTitleShow){var c=b.zoomDescr[$.axZm.zoomGA[$.axZm.zoomID].img]||"";if($.fancybox.init)$("#"+
	{"float":"fancybox-title-float-main",outside:"fancybox-title-outside",inside:"fancybox-title-inside",over:"fancybox-title-over"}[a.fancyBoxParam.boxTitlePosition]).html(c||""),c?$("#fancybox-title").show():"float"!=a.fancyBoxParam.boxTitlePosition&&"over"!=a.fancyBoxParam.boxTitlePosition||$("#fancybox-title").hide(),"float"==a.fancyBoxParam.boxTitlePosition&&$("#fancybox-title").css("left",$("#fancybox-wrap").outerWidth()/2-$("#fancybox-title").outerWidth()/2);else{var d=$(".fancybox-title");d.length&&
	(d.children().first().length?d.children().first().html(c):d.html(c))}}}},a.axZmCallBacks);$.fn.axZm.load({opt:c,path:a.axZmPath,parameter:"zoomFile="+b.zoomFile+(b.zoomData?"&zoomData="+b.zoomData:"&zoomDir="+b.zoomDir)+"&example="+a.exampleFancybox,divID:"axZmWrap",apiFullscreen:b.fullScreenApi})}else"colorbox"==b.ajaxZoomOpenMode&&($("#axZmTempBody, #axZmWrap").axZmRemove(!0),h=$("<div />").css({display:"none"}).attr("id","axZmWrap").appendTo("body"),c=$.fn.axZm.mergeCallBackObj({onStart:function(){h.css("display",
	"");var c={opacity:.9,initialWidth:300,initialHeight:300,preloading:!1,scrolling:!1,scrollbars:!1,title:a.colorBoxParam.title?b.zoomDescr[b.zoomFile.split("/").reverse()[0]]||"":!1,onCleanup:function(){$.fn.axZm.spinStop();$.fn.axZm.remove();$("#axZmTempBody").axZmRemove(!0);$("#axZmTempLoading").axZmRemove(!0);$("#axZmWrap").axZmRemove(!0)},inline:!0,href:"#axZmWrap",ajax:!1};$.colorbox($.extend(!0,{},a.colorBoxParam,c))},onImageChange:function(){a.colorBoxParam.title&&(b.zoomDescr[$.axZm.zoomGA[$.axZm.zoomID].img]?
	$("#cboxTitle").html(b.zoomDescr[$.axZm.zoomGA[$.axZm.zoomID].img]):$("#cboxTitle").html(""))}},a.axZmCallBacks),$.fn.axZm.load({opt:c,path:a.axZmPath,parameter:"zoomFile="+b.zoomFile+(b.zoomData?"&zoomData="+b.zoomData:"&zoomDir="+b.zoomDir)+"&example="+a.exampleColorbox,divID:"axZmWrap",apiFullscreen:b.fullScreenApi}))},E=function(a){var b=new Image;b.src=a;return b},n=function(){if(c.length){var e=f.css("boxSizing"),b=k?k:g;if(a.parentHeightRatio){if("auto"==a.parentHeightRatio){var d=b.innerWidth()*
	m/B;d>m&&(d=m)}else d=b.innerWidth()*a.parentHeightRatio;if("border-box"==e){var e=parseInt(f.css("border-top-width"))+parseInt(f.css("padding-top")),l=parseInt(f.css("border-bottom-width"))+parseInt(f.css("padding-bottom"));0<e&&(d+=e);0<l&&(d+=l)}"auto"==a.parentHeightRatio&&.5<a.maxHeight&&(e=$(window).height(),d>e*a.maxHeight&&(d=e*a.maxHeight));f.css("height",d)}else a.parentWidthRatio&&(d="auto"==a.parentWidthRatio?f.innerHeight()*B/m:f.innerHeight()*a.parentWidthRatio,"border-box"==e&&(e=parseInt(f.css("border-left-width")),
	l=parseInt(f.css("border-right-width")),0<e&&(d+=e),0<l&&(d+=l)),f.css("width",d));c.stop().css({width:"",height:"",maxWidth:"100%",maxHeight:"100%"});k||(r=c.width(),h=c.height(),t=r/h,u=b.innerHeight(),v=b.innerWidth(),f.innerWidth(),f.innerHeight(),c.css({left:(v-r)/2,top:(u-h)/2}));w=$(window).width();x=$(window).height()}},K=function(){var a=k?k:g;a.length&&u&&v&&w==$(window).width()&&x==$(window).height()&&(u==a.innerHeight()&&v==a.innerWidth()||n())},L=function(){"none"!=g.css("display")&&
	(A=!0,(q=c.attr("data-descr"))&&p.html(q).stop().animate({opacity:1},{easing:a.zoomEsingIn,duration:a.zoomInSpeed,complete:function(){p.css({opacity:""})}}),1!==a.zoomRatio&&c.stop().animate({height:h*a.zoomRatio,width:h*t*a.zoomRatio,maxWidth:100*a.zoomRatio+"%",maxHeight:100*a.zoomRatio+"%",marginLeft:(h*t-h*t*a.zoomRatio)/2,marginTop:(h-h*a.zoomRatio)/2},{easing:a.zoomEsingIn,duration:a.zoomInSpeed}),!0===a.overlay&&z.stop().animate({opacity:a.overlayOpacity},{easing:a.zoomEsingIn,duration:a.zoomInSpeed}))},
	M=function(){var e=!1;if("none"==g.css("display")&&(e=!0,!A))return;A=!1;q&&p.html(q).stop().animate({opacity:0},{easing:a.zoomEsingOut,duration:e?0:a.zoomOutSpeed});1!==a.zoomRatio&&c.stop().animate({maxWidth:"100%",maxHeight:"100%",height:h,width:r,marginLeft:0,marginTop:0},{easing:a.zoomEsingOut,duration:e?0:a.zoomOutSpeed,complete:function(){c.css({height:"",width:""})}});a.overlay&&z.stop().animate({opacity:0},{easing:a.zoomEsingOut,duration:e?0:a.zoomOutSpeed})};$("<img>").load(function(){!0===
	a.overlay&&(z=$("<div />").addClass("azHoverThumbOverlay").css({opacity:0,display:"block",backgroundColor:a.overlayColor}).appendTo(g));p=$("<div />").addClass("azHoverThumbDescr").css({display:"block",opacity:0}).appendTo(g);D=$("<div />").bind("click",I).addClass("azHoverThumbTrap").css({display:"block"}).appendTo(g);c.css({opacity:.01,display:"block"});B=c[0].naturalWidth||E(c.attr("src")).width;m=c[0].naturalHeight||E(c.attr("src")).height;n();w=$(window).width();x=$(window).height();browser.msie&&
	9>=browser.versionNumber?c.data("ieTimer",setInterval(K,500)):new resSensor(g[0],function(){w==$(window).width()&&x==$(window).height()&&n()});$(window).bind("resize",n);c.animate({opacity:1},{duration:a.fadeInSpeed,complete:function(){g.css("background-image","none");D.bind("mouseenter touchstart",L).bind("mouseleave touchend",M)}})}).attr("src",c.attr("src"))}})}});
    
    // ResizeSensor.js
	// Copyright (c) 2013 Marc J. Schmidt
	// License: http://www.opensource.org/licenses/mit-license.php
	(function(){this.resSensor=function(l,m){function n(){this.q=[];this.add=function(a){this.q.push(a)};var a,b;this.call=function(){a=0;for(b=this.q.length;a<b;a++)this.q[a].call()}}function p(a,b){return a.currentStyle?a.currentStyle[b]:window.getComputedStyle?window.getComputedStyle(a,null).getPropertyValue(b):a.style[b]}(function(a,b){if(!a.resizedAttached)a.resizedAttached=new n,a.resizedAttached.add(b);else if(a.resizedAttached){a.resizedAttached.add(b);return}a.resizeSensor=document.createElement("div");
a.resizeSensor.className="resize-sensor";a.resizeSensor.style.cssText="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: scroll; z-index: -1; visibility: hidden;";a.resizeSensor.innerHTML='<div class="resize-sensor-expand" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: scroll; z-index: -1; visibility: hidden;"><div style="position: absolute; left: 0; top: 0;"></div></div><div class="resize-sensor-shrink" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: scroll; z-index: -1; visibility: hidden;"><div style="position: absolute; left: 0; top: 0; width: 200%; height: 200%"></div></div>';
a.appendChild(a.resizeSensor);({fixed:1,absolute:1})[p(a,"position")]||(a.style.position="relative");var c=a.resizeSensor.childNodes[0],h=c.childNodes[0],d=a.resizeSensor.childNodes[1],e,f,g=function(){h.style.width=c.offsetWidth+10+"px";h.style.height=c.offsetHeight+10+"px";c.scrollLeft=c.scrollWidth;c.scrollTop=c.scrollHeight;d.scrollLeft=d.scrollWidth;d.scrollTop=d.scrollHeight;e=a.offsetWidth;f=a.offsetHeight};g();var k=function(a,b,c){a.attachEvent?a.attachEvent("on"+b,c):a.addEventListener(b,
c)};k(c,"scroll",function(){(a.offsetWidth>e||a.offsetHeight>f)&&a.resizedAttached.call();g()});k(d,"scroll",function(){(a.offsetWidth<e||a.offsetHeight<f)&&a.resizedAttached.call();g()})})(l,m)}})();
    
})(jQuery);