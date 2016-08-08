/*!
* Plugin: jQuery AJAX-ZOOM, jquery.axZm.hotspotEditor.js
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Extension Version: 1.5
* Extension Date: 2016-04-19
* URL: http://www.ajax-zoom.com
* Documentation: http://www.ajax-zoom.com/index.php?cid=docs
* Example: http://www.ajax-zoom.com/examples/example33.php
*/


;(function($){

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

	var loremImpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';	
	
	var setLorem = function(a){
		var b = $('#'+a).val() + '\n' + loremImpsum;
		$('#'+a).val(b);
	};
	
	var versioncompare = function(version1, version2){
		if (version1 == version2) {return 0;}
		var v1 = $.map(version1.split('.'), function(v){
			return parseInt(v, 10);
		}),v2 = $.map(version2.split('.'), function(v){
			return parseInt(v, 10);
		});

		var len = Math.max(v1.length, v2.length);
		for (var i = 0; i < len; i++) {
			v1[i] = v1[i] || 0;
			v2[i] = v2[i] || 0;
			if (v1[i] == v2[i]) {
				continue;
			}
			return v1[i] > v2[i] ? 1 : -1;
		}
		return 0;
	},
	
    getl = function (sep, str){
    	if (typeof str == 'string'){
        	return str.substring(str.lastIndexOf(sep)+1);
		}
		return str;
    },

    getf = function (sep, str){
    	if (typeof str == 'string'){
	        var extLen = getl(sep, str).length;
	        return str.substring(0, (str.length - extLen - 1));
		}
		return str;
    },
    
    isObject = function(x) {
        return (typeof x === 'object') && !(x instanceof Array) && (x !== null);
    },

	
	// Function to submit the JSON to a file which will save the information about hotspots e.g. to a javascript file
	saveHotspotJS = function(){
		$("#saveHotspotJS").submit(function(event){
			// stop form from submitting normally
			event.preventDefault(); 
				
			// get some values from elements on the page
			var Form = $(this),
				jsCode = $('#allHotspotsCode').val(),
				keepFormat = $('#jsKeepFormated').axZmGetPropType('checked') ? 1 : 0,
				fileName = $('#jsFileName').val(),
				password = $('#jsFilePass').val(),
				backup = $('#jsBackUp').axZmGetPropType('checked') ? 1 : 0,
				url = Form.attr('action');

			if (!fileName || !jsCode){
				$('#hotspotSaveToJSresults').empty().html('Please import your current settings into the formfield and define filename where this js should be saved!');
				return;
			}
			
			$('#hotspotSaveToJSresults').empty().html('Saving...');
			
			// Send the data using post and put the results in a div
			$.post(url, {jsCode: jsCode, keepFormat: keepFormat, fileName: fileName, password: password, backup: backup},
				function(data){
					$('#hotspotSaveToJSresults').empty().append(data);
				}
			).fail(function(a) {
				if (a.status == 500){
					$('#hotspotSaveToJSresults').empty().append('An error occured while sending data to '+url+' (status '+a.status+' '+a.statusText+'). Please make sure there are no PHP typo errors in this file!');
				} else if (a.status == 404){
					$('#hotspotSaveToJSresults').empty().append(url + ' was not found on this server (status '+a.status+' '+a.statusText+'). Please adjust the path to saveHotspotJS.php in the action attribute of the form with id "saveHotspotJS".');
				} else {
					$('#hotspotSaveToJSresults').empty().append('Error (status '+a.status+' '+a.statusText+'). Please check.');
				}
			});
		});	
	},
	
	// Returns the name of selected hotspot
	getHotspotSelector = function(id){
		id = id || 'hotspotSelector';
		return $('#'+id+' option:selected').val();
	},

	realTypeOf = function(v) {
		if (typeof(v) == "object") {
		if (v === null) return "null";
		if (v.constructor == (new Array).constructor) return "array";
		if (v.constructor == (new Date).constructor) return "date";
		if (v.constructor == (new RegExp).constructor) return "regex";
			return "object";
		}
		return typeof(v);
	},
	
	// Set current selected hotspot
	updateHotspotSelector = function(id, id2, noSpTo){
		id = id || 'hotspotSelector';
		id2 = id2 || 'hotspotSelector2';
		
		if ($.axZm && $.axZm.hotspots){
			var selected = getHotspotSelector(id);

			$('#'+id+' option').remove();
			$('#'+id2+' option').remove();
			
			//var sToHsJson = $('#scrollToHotspotJSON').empty();
			var sToHsJson = $('<div />');
			
			if ($.axZm.hotspots){
				$.each($.axZm.hotspots, function(hotspotName){
					var newOption = $('<option />');
					if (hotspotName == selected){newOption.attr('selected', true);}
					$('#'+id+', #'+id2).append(newOption.val(hotspotName).html(hotspotName));
					var aLink = $('<div />').html('	&darr; ' + hotspotName)
					.addClass('sToHsJsonBox')
					.bind('click', function(){findTextInTextArea('allHotspotsCode', '"'+hotspotName+'"')}).appendTo(sToHsJson);
				});
			}
				
			$('#scrollToHotspotJSON').empty().append(sToHsJson);
			
			$.fn.axZm.hotspotsDraggable(null, null, setStatus);
			colorSelectedHotspot(noSpTo);
			updateHotspotTooltip();
			
			if (selected !== undefined){
				$('#hotspotDeleteButton').val('Delete '+selected+' hotspot');
			}
		} else {
			$('#scrollToHotspotJSON').empty();
			$('#'+id+' option').remove();
			$('#'+id2+' option').remove();
			$('#hotspotDeleteButton').val('Delete hotspot');
		}
	},
	
	removeWarningNotSaved = function(){
		$('#saveWarningImg').remove();
	},
	
	setStatus = function(){
		if ($('#saveWarningImg').length != 0){return;}
		var imgTitleTag = 'Warning!\nNew hotspot settings have not been saved!\nClick to save.'
		$('#aZhS_tabs li:eq(3)').prepend($('<img>')
		.attr({
			src: $.axZm.icon +'batch_alert.png',
			id: 'saveWarningImg',
			align: 'left',
			alt: imgTitleTag,
			title: imgTitleTag
		}).bind('click', function(){
			$('#aZhS_tabs').tabs('select','#aZhS_tabs-5');
			$('#aZhS_save').tabs('select','#aZhS_save-2');
		}).css({width: 16, height: 16, marginTop: 5, marginLeft: 5, cursor: 'help'}));
	},
	
	// Save hotspot tooltip changed directly into
	saveHotspotTooltip = function(){
		var name = getHotspotSelector();
		if (name && $.axZm && $.axZm.hotspots){
			setStatus();
			$('#allHotspotsCode').val('');
			$('#applyJSON').css('backgroundColor', '#CCCCCC');
			
			$.axZm.hotspots[name]['enabled'] = $('#hotspot_enabled').axZmGetPropType('checked');
			
			var toolTipHtml = $('#hotspot_toolTipHtml').val();
			toolTipHtml = toolTipHtml.replace(/(\r\n|\n|\r)/gm,'').replace(/\"/g, '&#34;');
			$.axZm.hotspots[name]['altTitle']		= $('#hotspot_altTitle').val().replace(/\"/g, '&#34;');
			$.axZm.hotspots[name]['altTitleClass'] = $.trim($('#hotspot_altTitleClass').val()) || false;
			$.axZm.hotspots[name]['altTitleAdjustX'] = parseInt($('#hotspot_altTitleAdjustX').val());
			$.axZm.hotspots[name]['altTitleAdjustY'] = parseInt($('#hotspot_altTitleAdjustY').val());
			
			$.axZm.hotspots[name]['toolTipTitle'] 	= $('#hotspot_toolTipTitle').val().replace(/\"/g, '&#34;');
			$.axZm.hotspots[name]['toolTipHtml'] 	= toolTipHtml;
			$.axZm.hotspots[name]['toolTipGravity'] = $('#hotspot_toolTipGravity option:selected').val();	
			$.axZm.hotspots[name]['toolTipWidth'] 	= parseInt($('#hotspot_toolTipWidth').val());
			$.axZm.hotspots[name]['toolTipHeight'] 	= parseInt($('#hotspot_toolTipHeight').val());
			$.axZm.hotspots[name]['toolTipAdjustX'] = parseInt($('#hotspot_toolTipAdjustX').val());
			$.axZm.hotspots[name]['toolTipAdjustY'] = parseInt($('#hotspot_toolTipAdjustY').val());
			
			$.axZm.hotspots[name]['toolTipFullSizeOffset'] = parseInt($('#hotspot_toolTipFullSizeOffset').val());			
			$.axZm.hotspots[name]['toolTipOpacity'] = parseFloat($('#hotspot_toolTipOpacity').val());
			$.axZm.hotspots[name]['hotspotText'] = $('#hotspot_hotspotText').val() || false;

			$.axZm.hotspots[name]['toolTipCloseIcon'] = $('#hotspot_toolTipCloseIcon').val() || 'fancy_closebox.png';
			$.axZm.hotspots[name]['toolTipCloseIconPosition'] = $('#hotspot_toolTipCloseIconPosition option:selected').val();

			var toolTipCloseIconOffset = $('#hotspot_toolTipCloseIconOffset').val();
			if (toolTipCloseIconOffset || toolTipCloseIconOffset == 0){
				try{
					toolTipCloseIconOffset = eval('(' + (toolTipCloseIconOffset.replace(/(\r\n|\n|\r)/gm,'')) + ')');
				}catch(e){
					toolTipCloseIconOffset = parseInt(toolTipCloseIconOffset);
				}
				if (!isNaN(toolTipCloseIconOffset)){
					$.axZm.hotspots[name]['toolTipCloseIconOffset'] = toolTipCloseIconOffset;
				}else{
					$.axZm.hotspots[name]['toolTipCloseIconOffset'] = false;
				}
			}else{
				$.axZm.hotspots[name]['toolTipCloseIconOffset'] = false;
			}
			
			var toolTipGravFixed = $('#hotspot_toolTipGravFixed').axZmGetPropType('checked'),
				toolTipAutoFlip = $('#hotspot_toolTipAutoFlip').axZmGetPropType('checked');
			
			$.axZm.hotspots[name]['toolTipGravFixed'] = (toolTipGravFixed && toolTipGravFixed != 'undefined') ? true : false;
			$.axZm.hotspots[name]['toolTipAutoFlip'] = (toolTipAutoFlip && toolTipAutoFlip != 'undefined') ? true : false;
			$.axZm.hotspots[name]['toolTipAjaxUrl'] = $.trim($('#hotspot_toolTipAjaxUrl').val()) || false; 
			
			$.axZm.hotspots[name]['toolTipEvent'] = $('input[name=hotspot_toolTipEvent]:checked').val();
			
			$.axZm.hotspots[name]['toolTipTitleCustomClass'] = $.trim($('#hotspot_toolTipTitleCustomClass').val()) || false;
			$.axZm.hotspots[name]['toolTipCustomClass'] = $.trim($('#hotspot_toolTipCustomClass').val()) || false;
			$.axZm.hotspots[name]['hotspotTextFill'] = $('#hotspot_hotspotTextFill').axZmGetPropType('checked') ? true : false;
			$.axZm.hotspots[name]['hotspotTextClass'] = $.trim($('#hotspot_hotspotTextClass').val()) || false; 
			$.axZm.hotspots[name]['toolTipHideTimout'] = parseInt($('#hotspot_toolTipHideTimout').val()); 
			
			$.axZm.hotspots[name]['href'] = $.trim($('#hotspot_href').val()) || false;
			$.axZm.hotspots[name]['hrefTarget'] = $('#hotspot_hrefTarget').axZmGetPropType('checked') ? '_blank' : '';
			
			$.axZm.hotspots[name]['toolTipDraggable'] = $('#hotspot_toolTipDraggable').axZmGetPropType('checked');

			$.axZm.hotspots[name]['toolTipOverlayShow'] = $('#hotspot_toolTipOverlayShow').axZmGetPropType('checked');
			$.axZm.hotspots[name]['toolTipOverlayOpacity'] = parseFloat($('#hotspot_toolTipOverlayOpacity').val());
			$.axZm.hotspots[name]['toolTipOverlayColor'] = $.trim($('#hotspot_toolTipOverlayColor').val()) || '#000000';
			$.axZm.hotspots[name]['toolTipOverlayClickClose'] = $('#hotspot_toolTipOverlayClickClose').axZmGetPropType('checked');

			$.axZm.hotspots[name]['labelTitle'] = $.trim($('#hotspot_labelTitle').val()) || false; 
  			$.axZm.hotspots[name]['labelGravity'] = $('#hotspot_labelGravity option:selected').val();
			$.axZm.hotspots[name]['labelBaseOffset'] = parseInt($('#hotspot_labelBaseOffset').val()); 
			$.axZm.hotspots[name]['labelOffsetX'] = parseInt($('#hotspot_labelOffsetX').val());
			$.axZm.hotspots[name]['labelOffsetY'] = parseInt($('#hotspot_labelOffsetY').val());
			$.axZm.hotspots[name]['labelClass'] = $.trim($('#hotspot_labelClass').val()) || false; 
			$.axZm.hotspots[name]['labelOpacity'] = parseFloat($('#hotspot_labelOpacity').val());
			
			
			var hotspotTextCssObj ; 
			try{
				hotspotTextCssObj = eval('(' + ($('#hotspot_hotspotTextCss').val().replace(/(\r\n|\n|\r)/gm,'')) + ')');
			}catch(e){
				hotspotTextCssObj = {};
			}
			if (typeof(hotspotTextCssObj) == "object"){
				$.axZm.hotspots[name]['hotspotTextCss'] = hotspotTextCssObj;
			}else{
				$.axZm.hotspots[name]['hotspotTextCss'] = {};
			}
			
			// Events
			var eventList = ['mouseover', 'mouseout', 'mouseenter', 'mouseleave', 'mousedown', 'mouseup', 'click'];
			$.each(eventList, function(k,v){
				var filedValue = $('#hotspot_'+v).val();
				var thisFunction = null;
				if (filedValue){
					filedValue = filedValue.replace(/\"/g, '\'');
					
					try{
						thisFunction = eval('(' + (filedValue.replace(/(\r\n|\n|\r|\t)/gm,'')) + ')');
					}catch(e){
						thisFunction = null;
					}
										
					if (!$.isFunction(thisFunction)){
						try{
							thisFunction = eval('(' + ('function(){'+filedValue.replace(/(\r\n|\n|\r|\t)/gm,'')+'}') + ')');
						}catch(e){
							thisFunction = null;
						}
					}
					
					if ($.isFunction(thisFunction)){
						$.axZm.hotspots[name][v] = thisFunction;
					}else{
						$.axZm.hotspots[name][v] = null;
					}
				}else{
					$.axZm.hotspots[name][v] = null;
				}
			});
			
			var nameArr = [name];
			if ($('#hotspotApplyAll').axZmGetPropType('checked')){
				$('#hotspotApplyAll').attr('checked', false); // uncheck
				nameArr = [];
				$.each($.axZm.hotspots, function(a){nameArr.push(a)});
			}
						
			for (var i = 0; i < nameArr.length; i++) {
				var curName = nameArr[i];
				$.axZm.hotspots[curName]['width'] = parseInt($('#hotspot_width').val());
				$.axZm.hotspots[curName]['height'] = parseInt($('#hotspot_height').val());
				$.axZm.hotspots[curName]['hotspotImage'] = $.trim($('#hotspot_hotspotImage').val());
				$.axZm.hotspots[curName]['hotspotImageOnHover'] = $.trim($('#hotspot_hotspotImageOnHover').val());
				$.axZm.hotspots[curName]['zoomRangeMin'] = parseFloat($('#hotspot_zoomRangeMin').val());
				$.axZm.hotspots[curName]['zoomRangeMax'] = parseFloat($('#hotspot_zoomRangeMax').val());
				$.axZm.hotspots[curName]['gravity'] = $('#hotspot_gravity option:selected').val();
				$.axZm.hotspots[curName]['offsetX'] = parseInt($('#hotspot_offsetX').val());
				$.axZm.hotspots[curName]['offsetY'] = parseInt($('#hotspot_offsetY').val());
				$.axZm.hotspots[curName]['padding'] = parseInt($('#hotspot_padding').val());
				$.axZm.hotspots[curName]['opacity'] = parseFloat($('#hotspot_opacity').val());
				$.axZm.hotspots[curName]['opacityOnHover'] = parseFloat($('#hotspot_opacityOnHover').val());
				$.axZm.hotspots[curName]['zIndex'] = $('#hotspot_zIndex').val();
				$.axZm.hotspots[curName]['backColor'] = $.trim($('#hotspot_backColor').val());
				$.axZm.hotspots[curName]['borderWidth'] = parseInt($('#hotspot_borderWidth').val());
				$.axZm.hotspots[curName]['borderColor'] = $.trim($('#hotspot_borderColor').val());
				$.axZm.hotspots[curName]['borderStyle'] = $.trim($('#hotspot_borderStyle').val());
				$.axZm.hotspots[curName]['cursor'] = $.trim($('#hotspot_cursor').val());
			}
			

			
			$.fn.axZm.initHotspots();
			setTimeout(function(){
				$.fn.axZm.hotspotsDraggable(null, null, setStatus);
				colorSelectedHotspot(true);
			},100);
		}
	},
	
	// Set form fields values depending on loaded $.axZm.hotspots object
	updateHotspotTooltip = function(){
		var name = getHotspotSelector();
		if (name && $.axZm && $.axZm.hotspots && $.axZm.hotspots[name]){
			var thisNameObj = $.axZm.hotspots[name],
				enabled = thisNameObj['enabled'], 
				altTitle = thisNameObj['altTitle'],
				altTitleClass = thisNameObj['altTitleClass'],
				altTitleAdjustX = thisNameObj['altTitleAdjustX'],
				altTitleAdjustY = thisNameObj['altTitleAdjustY'],
				toolTipTitle = thisNameObj['toolTipTitle'],
				toolTipHtml = thisNameObj['toolTipHtml'],
				toolTipGravity = thisNameObj['toolTipGravity'],
				toolTipWidth = thisNameObj['toolTipWidth'],
				toolTipHeight = thisNameObj['toolTipHeight'],
				toolTipGravFixed = thisNameObj['toolTipGravFixed'],
				toolTipAutoFlip = thisNameObj['toolTipAutoFlip'],
				toolTipEvent = thisNameObj['toolTipEvent'],
				toolTipAdjustX = thisNameObj['toolTipAdjustX'],
				toolTipAdjustY = thisNameObj['toolTipAdjustY'],
				toolTipFullSizeOffset = thisNameObj['toolTipFullSizeOffset'],
				toolTipOpacity = thisNameObj['toolTipOpacity'],
				toolTipTitleCustomClass = thisNameObj['toolTipTitleCustomClass'],
				toolTipCustomClass = thisNameObj['toolTipCustomClass'],
				hotspotText = thisNameObj['hotspotText'],
				hotspotTextClass = thisNameObj['hotspotTextClass'],
				hotspotTextFill = thisNameObj['hotspotTextFill'],
				hotspotTextCss = thisNameObj['hotspotTextCss'],
				toolTipHideTimout = thisNameObj['toolTipHideTimout'],
				toolTipCloseIcon = thisNameObj['toolTipCloseIcon'],
				toolTipCloseIconOffset = thisNameObj['toolTipCloseIconOffset'],
				toolTipDraggable = thisNameObj['toolTipDraggable'],
				toolTipOverlayShow = thisNameObj['toolTipOverlayShow'], 
				toolTipOverlayOpacity = thisNameObj['toolTipOverlayOpacity'],  
				toolTipOverlayColor = thisNameObj['toolTipOverlayColor'], 
				toolTipOverlayClickClose = thisNameObj['toolTipOverlayClickClose'],
				toolTipAjaxUrl = thisNameObj['toolTipAjaxUrl'],
				width = thisNameObj['width'],
				height = thisNameObj['height'],
				hotspotImage = thisNameObj['hotspotImage'],
				hotspotImageOnHover = thisNameObj['hotspotImageOnHover'],
				zoomRangeMin = thisNameObj['zoomRangeMin'],
				zoomRangeMax = thisNameObj['zoomRangeMax'],
				gravity = thisNameObj['gravity'],
				offsetX = thisNameObj['offsetX'],
				offsetY = thisNameObj['offsetY'],
				padding = thisNameObj['padding'],
				opacity = thisNameObj['opacity'],
				opacityOnHover = thisNameObj['opacityOnHover'],
				zIndex = thisNameObj['zIndex'],
				backColor = thisNameObj['backColor'],
				borderWidth = thisNameObj['borderWidth'],
				borderColor = thisNameObj['borderColor'],
				borderStyle = thisNameObj['borderStyle'],
				cursor = thisNameObj['cursor'],
				labelTitle = thisNameObj['labelTitle'],
				labelGravity = thisNameObj['labelGravity'],
				labelBaseOffset = thisNameObj['labelBaseOffset'],
				labelOffsetX = thisNameObj['labelOffsetX'],
				labelOffsetY = thisNameObj['labelOffsetY'],
				labelClass = thisNameObj['labelClass'],
				labelOpacity = thisNameObj['labelOpacity'];
			
			if (toolTipHtml && $.isFunction(style_html)){
				
				toolTipHtml = style_html(toolTipHtml, {
				  'indent_size': 2,
				  'indent_char': ' ',
				  'max_char': false,
				  'brace_style': 'expand',
				  'unformatted': ['a', 'sub', 'sup', 'b', 'i', 'u']
				});
				toolTipHtml = toolTipHtml.replace(/href: /, 'href:');
				toolTipHtml = toolTipHtml.replace(/\( \'/, '(\'');
				toolTipHtml = toolTipHtml.replace(/\' \)\'/, '\')');
			}
			
			if (toolTipHtml){
				toolTipHtml = toolTipHtml.replace(/&#34;/g,'\"');
			}
			
			if (toolTipTitle){
				toolTipTitle = toolTipTitle.replace(/&#34;/g,'\"');
			}
			
			$('#hotspot_enabled').attr('checked', enabled ? true : false);
			$('#hotspot_labelTitle').val(labelTitle || '');
			$('#hotspot_labelGravity').val(labelGravity || 'right');
			$('#hotspot_labelBaseOffset').val(labelBaseOffset || 0);
			$('#hotspot_labelOffsetX').val(labelOffsetX || 0);
			$('#hotspot_labelOffsetY').val(labelOffsetY || 0);
			$('#hotspot_labelClass').val(labelClass || '');
			$('#hotspot_labelOpacity').val(labelOpacity || 1.0);
			
			$('#hotspot_width').val(width);
			$('#hotspot_height').val(height);
			$('#hotspot_hotspotImage').val(hotspotImage || '');
			$('#hotspot_hotspotImageOnHover').val(hotspotImageOnHover || '');
			$('#hotspot_zoomRangeMin').val(zoomRangeMin || 0);
			$('#hotspot_zoomRangeMax').val(zoomRangeMax || 1);
			$('#hotspot_gravity').val(gravity || 'center');
			$('#hotspot_offsetX').val(offsetX);
			$('#hotspot_offsetY').val(offsetY);
			$('#hotspot_padding').val(padding);
			$('#hotspot_opacity').val(opacity);
			$('#hotspot_opacityOnHover').val(opacityOnHover);
			$('#hotspot_zIndex').val(zIndex);
			$('#hotspot_backColor').val(backColor);
			$('#hotspot_borderWidth').val(borderWidth);
			$('#hotspot_borderColor').val(borderColor);
			$('#hotspot_borderStyle').val(borderStyle);
			$('#hotspot_cursor').val(cursor);
			$('#hotspot_toolTipAjaxUrl').val(toolTipAjaxUrl || '');
			$('#hotspot_toolTipOverlayShow').attr('checked', toolTipOverlayShow ? true : false);
			$('#hotspot_toolTipDraggable').attr('checked', toolTipDraggable ? true : false);
			$('#hotspot_hotspotTextFill').attr('checked', hotspotTextFill ? true : false);
			$('#hotspot_toolTipOverlayClickClose').attr('checked', toolTipOverlayClickClose ? true : false);
			$('#hotspot_toolTipOverlayOpacity').val(toolTipOverlayOpacity || 0.75);
			$('#hotspot_toolTipOverlayColor').val(toolTipOverlayColor || '#000000');
			
			if (typeof(hotspotTextCss) != 'object' || $.isEmptyObject(hotspotTextCss) || !hotspotTextCss){hotspotTextCss = {};}
			$('#hotspot_toolTipCloseIcon').val(toolTipCloseIcon || 'fancy_closebox.png');
			
			if (toolTipCloseIconOffset !== false && !isNaN(toolTipCloseIconOffset)){
				$('#hotspot_toolTipCloseIconOffset').val(toolTipCloseIconOffset.toString());
			}else{
				$('#hotspot_toolTipCloseIconOffset').val('');
			}

			$('#hotspot_toolTipHideTimout').val(toolTipHideTimout >= 0 ? toolTipHideTimout : 1000);
			$('#hotspot_toolTipTitleCustomClass').val(toolTipTitleCustomClass || '');
			$('#hotspot_toolTipCustomClass').val(toolTipCustomClass || '');
			$('#hotspot_altTitle').val(altTitle || '');
			$('#hotspot_altTitleClass').val(altTitleClass || '');
			$('#hotspot_altTitleAdjustX').val(parseInt(altTitleAdjustX));
			$('#hotspot_altTitleAdjustY').val(parseInt(altTitleAdjustY));
			$('#hotspot_toolTipTitle').val(toolTipTitle || '');
			
			var hotspot_toolTipHtml_field =  $('#hotspot_toolTipHtml');
			if (hotspot_toolTipHtml_field.data('cleditor')){
				resetWYSIWYG(hotspot_toolTipHtml_field);
				$('#hotspot_toolTipHtml').val(toolTipHtml || '');
				//toggleWYSIWYG();
			}else{
				$('#hotspot_toolTipHtml').val(toolTipHtml || '');
			}
			

			$('#hotspot_toolTipGravity').val(toolTipGravity || 'hover');
			$('#hotspot_toolTipWidth').val(toolTipWidth || 250);
			$('#hotspot_toolTipHeight').val(toolTipHeight || 120);
			$('#hotspot_toolTipGravFixed').attr('checked', toolTipGravFixed);
			$('#hotspot_toolTipAutoFlip').attr('checked', toolTipAutoFlip);
			$('#hotspot_href').val(thisNameObj['href'] || '');
			$('#hotspot_hrefTarget').attr('checked', thisNameObj['hrefTarget'] == '_blank' ? true : false);
			$('#hotspot_toolTipAdjustX').val(toolTipAdjustX);
			$('#hotspot_toolTipAdjustY').val(toolTipAdjustY);
			$('#hotspot_toolTipFullSizeOffset').val(toolTipFullSizeOffset);			
			
			$('#hotspot_toolTipOpacity').val(toolTipOpacity);
			$('#hotspot_hotspotText').val(hotspotText || '');
			$('#hotspot_hotspotTextClass').val(hotspotTextClass || ''); 
			$('#hotspot_hotspotTextCss').val(typeof(hotspotTextCss) == 'object' ? $.toJSON(hotspotTextCss) : '{}');
			$('input:radio[name="hotspot_toolTipEvent"]').filter('[value="'+toolTipEvent+'"]').attr('checked', true);

			var eventList = ['mouseover', 'mouseout', 'mouseenter', 'mouseleave', 'mousedown', 'mouseup', 'click'];
			$.each(eventList, function(k,v){
				var filedValue = $('#hotspot_'+v).val();
				var thisFunction = null;
 				if ($.isFunction(thisNameObj[v])){
					var strJS = thisNameObj[v].toString().replace(/(\r\n|\n|\r|\t)/gm,"");
					if ($.isFunction(js_beautify)){
						strJS = js_beautify(strJS, {
							'indent_size': 1,
					  		'indent_char': '\t'
						});
					}
					$('#hotspot_'+v).val(strJS);
				}else{
					$('#hotspot_'+v).val('');
				}
			});
		
		}
	},
		
	// Format JSON object to display in textarea
	FormatJSON = function(oData, sIndent, placebo){
		if (placebo){
			sHTML = $.toJSON(oData);
			sHTML = sHTML.replace(/\"function/g, 'function');
			sHTML = sHTML.replace(/}\"/g, '}');
			return sHTML;
		}
		
		if (arguments.length < 2) {
			var sIndent = "";
		}
		
		var sIndentStyle = "	";
		var sDataType = realTypeOf(oData);
	
		// open object
		if (sDataType == "array") {
			if (oData.length == 0) {
				return "[]";
			}
			var sHTML = "[";
		} else {
			var iCount = 0;
			$.each(oData, function() {
				iCount++;
				return;
			});
			if (iCount == 0) { // object is empty
				return "{}";
			}
			var sHTML = "{";
		}
	
		// loop through items
		var iCount = 0;
		$.each(oData, function(sKey, vValue) {
			if (iCount > 0) {
				sHTML += ",";
			}
			if (sDataType == "array") {
				sHTML += ("\n" + sIndent + sIndentStyle);
			} else {
				sHTML += ("\n" + sIndent + sIndentStyle + "\"" + sKey + "\"" + ": ");
			}
	
			// display relevant data type
			switch (realTypeOf(vValue)) {
				case "array":
					break;
				case "object":
					sHTML += FormatJSON(vValue, (sIndent + sIndentStyle));
					break;
				case "boolean":
					sHTML += vValue.toString();
					break;
				case "number":
					sHTML += vValue.toString();
					break;
				case "null":
					sHTML += "null";
					break;
				case "string":
					sHTML += ("\"" + vValue.replace(/\"/g, '&#34;') + "\""); // &#34;
					break;
				default:
					sHTML += ("TYPEOF: " + typeof(vValue));
			}
			iCount++;
		});
	
		if (sDataType == "array") {
			sHTML += ("\n" + sIndent + "]");
		} else {
			sHTML += ("\n" + sIndent + "}");
		}
	
		sHTML = sHTML.replace(/\"function/g, 'function');
		sHTML = sHTML.replace(/}\"/g, '}');
		sHTML = sHTML.replace(/;    /g, '; ');
	
		return sHTML;
	},

	iconImage = function(img){
		if (!img){return '';}
		
		if (img.substr(0,4) == 'http' || img.substr(0,1) == '/'){
			return img;
		}else{
			return $.axZm.icon+img;		
		}
	},

	// Change hotspots color after it is selected, 
	// spin to the first available frame with that hotspot and 
	// center it if zoomed
	colorSelectedHotspot = function(noSpTo){
		if (!$.axZm || !$.axZm.icon || !$.axZm.hotspots){return;}
		
		setTimeout(function(){
			var selectedHotspot = getHotspotSelector(),
				defGreen = 'hotspot64_green.png',
				defRed = 'hotspot64_red.png',
				defaultGreen = $.axZm.icon+defGreen,
				defaultRed = $.axZm.icon+defRed,
				shown = false,
				visible = false;
				
			$('#hotspotDeleteButton').val('Delete '+selectedHotspot+' hotspot');

			$.each($.axZm.hotspots, function(name, values){
				if (values.shape == 'point'){
					var hotspotImage = $.axZm.hotspots[name]['hotspotImage'];
					
					if (hotspotImage){
						if (selectedHotspot == name){
							$('#axZmHotspotImg_'+name).attr('src', defaultRed);
							$('#hotspotImgPreview').empty();
							$('<img>').attr('src', iconImage(hotspotImage)).css({
								width: values.width,
								height: values.height,
								float: 'right'
							}).bind('click', function(){
								$(this).remove();
								alert('The preview hotspot image has been removed');
							}).appendTo('#hotspotImgPreview');
							
						}else{
							// apply only on default hotspots
							if (hotspotImage.indexOf(defGreen) >= 0 || hotspotImage.indexOf(defRed) >= 0){
								$('#axZmHotspotImg_'+name).attr('src', defaultGreen);
							}else{
								$('#axZmHotspotImg_'+name).attr('src', iconImage(hotspotImage));
							}
						}
					} else {
						if (selectedHotspot == name){
							$('#hotspotImgPreview').empty();
						}
					}
					
				} else if (values.shape == 'rect'){
					if (selectedHotspot == name){
						$('#axZmHotspot_'+name).css('borderColor', 'red');
						$('#hotspotImgPreview').empty();
					}else{
						$('#axZmHotspot_'+name).css('borderColor', $.axZm.hotspots[name]['borderColor']);
					}
				}
			});
			
			if ($.axZm.hotspots && $.axZm.hotspots[selectedHotspot]){
				var selHotspotData = $.axZm.hotspots[selectedHotspot];
				
				if (selHotspotData && !noSpTo){
					$.fn.axZm.zoomToHotspot(selectedHotspot, {
						findMiddle: true,
						spinAndZoom: true,
						zoomLevel: (selHotspotData.zoomRangeMin == 0 && selHotspotData.zoomRangeMax == 100) ? '1%' : null
					});
				}
			}
			updateHotspotTooltip();
			
		}, 150);
		
		
	},
	
	scrollToOptDocu = function(val){
		$('#aZhS_tabs').tabs('select','#aZhS_tabs-8'); 
		$('#aZhS_about').tabs('select','#aZhS_about-3');
		//self.location.href = '#hsOpt_'+val;
		$.scrollTo('#hsOpt_'+val, 350);
		$('#docuTable tr').css('backgroundColor', '');
		$('#hsOpt_'+val).parent().css('backgroundColor', '#FAFFAD');
	},
	
	// Load a different set of 3D/360 or 2D
	changeAxZmContentPHP = function(){
		if (typeof ajaxZoom !== 'undefined'){
			if ($.axZm.spinPreloading){
				alert('Please wait...');
				return;
			}
			
			$('#pathToParameter').empty().removeAttr('style').removeAttr('class');
			
			var pathToLoad = '',
				pathToLoad2D = $('#pathToLoad2D').val().replace(/(\"|\')/gm,''),
				pathToLoad360 = $('#pathToLoad360').val().replace(/(\"|\')/gm,''),
				hotspotFileToLoad = $('#hotspotFileToLoad').val();
			
			if (pathToLoad2D && pathToLoad360){
				alert('You have to decide whether to load 2D or 360/3D');
				return;
			} else if (!pathToLoad2D && !pathToLoad360){
				alert('Please enter the Path for 2D or Path for 360/3D');
				return;
			}
			
			if (pathToLoad2D || pathToLoad360){
				if ($('#saveWarningImg').length){
				    var confirmationMessage = "It looks like you have been editing something.\r\n";
				    confirmationMessage += "If you load something different or reload before saving,\r\nyour changes will be lost!\r\n\r\n";
				    confirmationMessage += "Do you really want to proceed?";
					if (!confirm(confirmationMessage)){
						return false;
					}
				}
			
				$.fn.axZm.spinStop();
				
				// Define callbacks
				var myCallBacks = {
					 
					onLoad: function(){ // onSpinPreloadEnd
						$.axZm.spinReverse = false;
						// Load hotspots over this function... or just define $.axZm.hotspots here and trigger $.fn.axZm.initHotspots(); after this.

						$.fn.axZm.loadHotspotsFromJsFile(hotspotFileToLoad, false, function(){
							var getHotspotJsFile = $.fn.axZm.getHotspotJsFile(true);
							
							// Path is adjustable in saveHotspotJS.php so get only filename
							getHotspotJsFile = getf('.', getl('/', getHotspotJsFile));

							$('#jsFileName').val(getHotspotJsFile ? getHotspotJsFile : '');
							
							// This is just for hotspot editor
							if (typeof updateHotspotSelector !== 'undefined' ){
								setTimeout(updateHotspotSelector, 200);
							}				
						});
 
						$('#newHotspotAllFrames').attr('checked', $.axZm.spinMod ? true : false);
					}
					 
				};
				
				// Load / Reload AJAX-ZOOM
				function ajaxZoomReload(){
					pathToLoad = pathToLoad2D ? pathToLoad2D : pathToLoad360;
					
					var url = ajaxZoom.path + 'zoomLoad.php';
					var qStringPar = '3dDir';
					
					// check path to load and change 3dDir= to zoomData=
					if (pathToLoad2D && (/\.(gif|png|jp(e|g|eg)|tif|tiff|psd|bmp)((#|\?).*)?$/i.test(pathToLoad2D))){
						qStringPar = 'zoomData';
					} else if (pathToLoad2D) {
						qStringPar = 'zoomDir';
					}

					pathToLoad = pathToLoad.replace(/(zoomDir=|zoomData=|3dDir=|\"|\')/gm,'');
					
					var parameter = 'zoomLoadAjax=1&example=hotSpotEdit&'+qStringPar+'='+pathToLoad;
					
					showRealQueryPath('example=hotSpotEdit&'+qStringPar+'='+pathToLoad);
					
					if ($.axZm){
						delete $.axZm.hotspots;
						$('#allHotspotsCode').val('');
						$('#scrollToHotspotJSON').empty();
						$('#saveWarningImg').remove();
						updateHotspotSelector();
					}
					
					$.fn.axZm.load({
			            opt: myCallBacks,
			            path: ajaxZoom.path,
			            parameter: parameter,
			            divID: ajaxZoom.divID
					});
				}
				ajaxZoomReload();
			}
		}
	},
	
	getLoadedParameters = function(){
		var parToPass = $.axZm.parToPass,
			retString = '',
			parToPassArr = parToPass.split('&');
			
		$.each(parToPassArr, function(k, v){
			if (/(example=|zoomDir=|zoomData=|3dDir=)/gm.test(v)){
				retString += retString ? '&'+v : v;
				if (v.indexOf('3dDir=') != -1){
					$('#pathToLoad360').val(v.replace(/(3dDir=)/gm,''));
					$('#pathToLoad2D').val('');
				} else if (/(zoomDir=|zoomData=)/gm.test(v)){
					$('#pathToLoad2D').val(v.replace(/(zoomDir=|zoomData=)/gm,''));
					$('#pathToLoad360').val('');
				}
			}
		});
		var getHotspotJsFile = $.fn.axZm.getHotspotJsFile();
		$('#hotspotFileToLoad').val(getHotspotJsFile ? getHotspotJsFile : '');

		showRealQueryPath(retString);
	},

	showRealQueryPath = function(retString){
		$('#pathToParameter').addClass('azMsg').
		html("Below is the parameter which is passed to AJAX-ZOOM. You should change the value of <code>example=</code> \
		depending on the final configuration / example you will be using. \
		For more info see <a href=\"javascript: void(0)\" \
		onclick=\"jQuery('#aZhS_tabs').tabs('select','#aZhS_tabs-8'); jQuery('#aZhS_about').tabs('select','#aZhS_about-2');\"> \
		About -> Code example</a> tab around line 52 (ajaxZoom.parameter); \
		<div style=\"margin: 5px 0px 5px 0px;\"><code style=\"padding: 5px; background-color: #FFFFFF\">"+retString+"</code></div>");
	},
	
	// Print JSON object as text into textarea
	importJSON = function(){
		var jsonObj = $.fn.axZm.getHotspotObj( $('#allHotspotsCodeDefaults').axZmGetPropType('checked'), true, $('#allHotspotsCodeImgNames').axZmGetPropType('checked') );
		jsonObj = FormatJSON(jsonObj, '', $('#allHotspotsCodeFormat').axZmGetPropType('checked'));
		$('#allHotspotsCode').val(jsonObj);
		$('#applyJSON').css('backgroundColor', '#CCCCCC');
	},
	
	// Print only coordinates as text into textarea
	importJSONcoordinates = function(){
		$('#currentHotspotPositions').val( 
			$.aZhSpotEd.FormatJSON( 
				$.fn.axZm.getHotspotPositions($.aZhSpotEd.getHotspotSelector(), 
				$('#currentHotspotPositionsImgNames').axZmGetPropType('checked')), 
				'', 
				$('#currentHotspotPositionsFormat').axZmGetPropType('checked') 
			) 
		);
	},
	
	// Save configuration to JS file
	saveJSONtoFile = function(){
		$('#hotspotSaveToJSresults').css('padding', 7); 
		var code = $('#allHotspotsCode').val(),
		update = $('#jsAutoImport').axZmGetPropType('checked') ? 1 : 0;
		
		if (update || !code || code == '{}'){
			importJSON();
			setTimeout(function(){
				$('#saveHotspotJS').submit();
			}, 100);
		}else{
			var newObj;
			try{
				newObj = eval('(' + ($('#allHotspotsCode').val().replace(/(\r\n|\n|\r)/gm,'') || '{}') + ')');
			}catch(e){
				alert(e);
			}
			if (typeof(newObj) == "object"){
				applyJSON();
				setTimeout(function(){
					removeWarningNotSaved();
					$('#saveHotspotJS').submit();
				},100);
			}
		}
		
	},
	
	// Load / apply JSON typed into a textfield
	applyJSON = function(){
		var newObj;
		try{
			newObj = eval('(' + ($('#allHotspotsCode').val().replace(/(\r\n|\n|\r)/gm,'') || '{}') + ')');
		}catch(e){
			alert(e);
		}
		
		if (typeof(newObj) == "object"){
			$.fn.axZm.initHotspots( newObj );
			$('#applyJSON').css('backgroundColor', '#CCCCCC');
		}
		
		setStatus();
		updateHotspotSelector(); 
		colorSelectedHotspot(true);
		
		if ( $('#keepDraggable').axZmGetPropType('checked') ){
			setTimeout(function(){
				$.fn.axZm.hotspotsDraggable(null, null, setStatus);
			}, 500);
		}
		 
	},

	deleteHotspot = function(){
		if (confirm('Do you really want to delete\n'+getHotspotSelector()+' hotspot?')) { 
			$.fn.axZm.showHotspotLayer(); 
			$.fn.axZm.deleteHotspot(getHotspotSelector()); 
			updateHotspotSelector(); 
		}
	},
	
	isInt = function(x) {
		var y = parseInt(x);
		if (isNaN(y)) return false;
		return x==y && x.toString()==y.toString();
	}, 

	addNewHotspot = function(){
		$.fn.axZm.showHotspotLayer(); 
		$('#allHotspotsCode').val('');
		setStatus();
		
		var newHotspotName = $('#fieldNewHotSpotName').val().replace(/\s+/g, '') || 'Rand_'+Math.random().toString(36).substring(2),
			posObj = {},
			l = $.trim($('#fieldRectLeft').val()),
			t = $.trim($('#fieldRectTop').val()),
			w = $.trim($('#fieldRectWidth').val()),
			h = $.trim($('#fieldRectHeight').val()),
			newHotspotObj = {
				name: newHotspotName,
				posObj: false,
				autoPos: $('#newHotspotAllFrames').axZmGetPropType('checked'),
				draggable: true,
				autoTitle: $('#newHotspotAltTitle').axZmGetPropType('checked'),
				settings: {
					shape: $('input[name=hotspotShape]:checked').val()
				}
			};
				
		if (l && t || (isInt(l) && isInt(t))){
			if (newHotspotObj.autoPos){
				if (newHotspotObj.settings.shape == 'point'){
					$.each($.axZm.zoomGA, function(k, v){
						posObj[k] = {left: l, top: t};  
					});
				}else{
					if (w && h){
						$.each($.axZm.zoomGA, function(k, v){
							posObj[k] = {left: l, top: t, width: w, height: h};
						});
					}else{
						posObj = false;
					}
				}
			}else{
				if (newHotspotObj.settings.shape == 'point'){
					posObj[$.axZm.zoomID] = {left: l, top: t};
				}else{
					if (w && h){
						posObj[$.axZm.zoomID] = {left: l, top: t, width: w, height: h};
					}else{
						posObj = false;
					}
				}
			}
		}else{
			posObj = false;
		}
		
		if (newHotspotObj.settings.shape == 'rect'){
			if ($('#fieldHotspotTextFill').axZmGetPropType('checked')){
				newHotspotObj.settings.hotspotTextFill = true;
			}
			
			if ($('#fieldHotspotTextClass').val()){
				newHotspotObj.settings.hotspotTextClass = $('#fieldHotspotTextClass').val();
			}

			var hotspotTextCssObj; 
			try{
				hotspotTextCssObj = eval('(' + ($('#fieldHotspotTextCss').val().replace(/(\r\n|\n|\r)/gm,'')) + ')');
			}catch(e){
				hotspotTextCssObj = {};
			}
			
			if (hotspotTextCssObj){
				newHotspotObj.settings.hotspotTextCss = hotspotTextCssObj;
			}
		}
 
		newHotspotObj.posObj = posObj;
		$.fn.axZm.createNewHotspot(newHotspotObj);
		
		
		updateHotspotSelector(null, null, true);
		
		$('#hotspotSelector [value='+newHotspotName+']').prop('selected', true); 
		$('#hotspotSelector2 [value='+newHotspotName+']').prop('selected', true); 
		
		$('#fieldNewHotSpotName').val('');
	},
	
	findTextInTextArea = function(textAreaID, searchWord){
		var ref = $('#'+textAreaID);
		if (ref.val() == ''){
			importJSON();
		}
		var posi = ref.val().indexOf(searchWord);
		if (posi != -1) {
			ref[0].focus();
			if (browser.msie){
				var r = ref[0].createTextRange();
				r.collapse(true);
				r.moveStart('character', posi);
				r.moveEnd('character',  searchWord.length);
				r.select();				
			}else{
				ref[0].setSelectionRange(posi, posi + searchWord.length);
			}
		}
	},
	
	toggleNaviBar = function(a){
		if ($('#testCustomNavi').css('display') == 'block'){
			$('#axZm_zoomCustomNavi, #testCustomNavi').css('display', 'none');
			$(a).html('activate');
		}else{
			$('#axZm_zoomCustomNavi, #testCustomNavi').css('display', '');
			$(a).html('deactivate');
		}
	},
	
	resetWYSIWYG = function(o){
		if (!(o)){o = $('#hotspot_toolTipHtml');}
		if (o && o.data('cleditor')){
			var v = o.val(), p = o.parent().parent();
			o.parent().remove();
			$('<textarea id="hotspot_toolTipHtml" style="width: 100%; height: 250px"></textarea>').appendTo(p).val(v);
		}
	},
	
 	toggleWYSIWYG = function(){
		var o = $('#hotspot_toolTipHtml');

		if (!o.data('cleditor')){
			o.cleditor({
				height: 350,
				docCSSFile: ajaxZoom.path+'/axZm.css'.replace('//','/'),
				updateFrame: function(a){
					setTimeout(
						function(){
							// Apply same classes to WYSIWYG
							$('iframe:eq(0)', $('#hotspot_toolTipHtml_parent'))
							.contents().find('body')
							.css({overflowY: 'scroll', position: 'static', font: ''})
							.addClass('axZmToolTipInner')
							.css({height: 280})
					}, 0); 
					return a;
				}
			})
		}else{
			resetWYSIWYG(o);
		}
	},

	displayDocu = function(how){
		if ($('#hotspotsDocuPopup').length){
			$('#hotspotsDocuPopup').remove();
			return;
		}
		var ww = $(window).width() - $('#aZhS_tabs').outerWidth() - 25;
		if (!how){how = 'popup';}
		if ((how == 'left' && ww < $.axZm.boxW) || how == 'popup'){
			jQuery.openAjaxZoomInFancyBox({href: 'docu_wrap.php?docu=hotspots', iframe: true, scrolling: 'yes'})
			return;
		}
		
		$('<div />').attr('id', 'hotspotsDocuPopup').css({
		  position: 'fixed',
		  left: 0,
		  zIndex: 5555,
		  top: 0,
		  width: ww,
		  height: '100%',
		  backgroundColor: '#FFF'
		})
		.html('<iframe src="docu_wrap.php?docu=hotspots" scrolling="yes" style="height: 100%; width: 100%" frameborder="0" hspace="0"></iframe>')
		.appendTo('body');
		

		$('<div />').addClass('docuCloseButton')
		.html('close')
		.bind('click', function(){$('#hotspotsDocuPopup')
		.remove()})
		.appendTo('#hotspotsDocuPopup')
		.css('right', -($('.docuCloseButton').outerWidth()))
		.css('top', 8)
	}
	
	// Public access
	$.aZhSpotEd = {
		toggleWYSIWYG: toggleWYSIWYG,
		displayDocu: function(a){displayDocu(a)},
		getl: function(a,b){return getl(a,b)},
		getf: function(a,b){return getf(a,b)},
		changeAxZmContentPHP: changeAxZmContentPHP,
		getLoadedParameters: getLoadedParameters,
		colorSelectedHotspot: function(a){return colorSelectedHotspot(a)},
		saveHotspotJS: saveHotspotJS,
		saveHotspotTooltip: saveHotspotTooltip,
		updateHotspotTooltip: updateHotspotTooltip,
		FormatJSON: function(a, b, c){return FormatJSON(a,b,c)},
		getHotspotSelector: function(a){return getHotspotSelector(a)},
		realTypeOf: function(a){return realTypeOf(a)},
		updateHotspotSelector: function(a, b, c){updateHotspotSelector(a, b, c)},
		importJSON: importJSON,
		importJSONcoordinates: importJSONcoordinates, 
		addNewHotspot: addNewHotspot,
		saveJSONtoFile: saveJSONtoFile,
		applyJSON: applyJSON,
		removeWarningNotSaved: removeWarningNotSaved,
		deleteHotspot: deleteHotspot,
		setLorem: function(a){setLorem(a)},
		scrollToOptDocu: function(a){scrollToOptDocu(a)},
		findTextInTextArea: function(a, b){findTextInTextArea(a, b)},
		toggleNaviBar: function(a){toggleNaviBar(a)}
	};

	$.fn.axZmGetPropType = function(type){
		var oldJQuery = versioncompare('1.6', $.fn.jquery) > 0 ? true : false;
		if (oldJQuery){return $(this).attr(type);}else{return $(this).prop(type);}
	};

	
    $.fn.azSimpleToolTip = function() {
		var $this = this,
			deflt = $this.data('optDeflt'),
			text = $this.data('optDescr'),
			offsetX = 40,
			offsetY = 10,
			winW, winH, scrollTop, height, width,
			icon = 'data:image/png;base64,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';

		var toolTip = $('<div />')
		.addClass('axZmHoverTooltip ui-corner-all someShadow')
		.css({width: 300, maxWidth: 300, 
			 height: 'auto', fontFamily: 'Arial', fontWeight: 'normal', position: 'absolute', 
			 zIndex: 555, background: 'none #8DB8D7', borderColor: '#267CB8'});

		var setPos = function(e){
			if (e.pageY){
				var left = e.pageX + offsetX;
				var top = e.pageY + offsetY;
				if (left + width > winW){
					left = e.pageX - offsetX - width + 16;
				}
				if (top + height > winH + scrollTop){
					top = e.pageY - offsetY - height;
				}
				toolTip.css({
					top: top,
					left: left
				});
			}
		};
		
		if (text){
			$this.bind('touchstart mouseenter', function(e){
				$('.axZmHoverTooltip').not(toolTip).remove();
				if (e.pageY){
					toolTip.html('<div style="padding-left: 30px; padding-top: 4px; height: 20px; font-weight: bold; font-size: 14px; color: #555555; margin-bottom: 5px; background: url('+icon+') no-repeat left">'
					+$this.text()+'</div><div class="" style="background-color: #ffffff; padding: 5px 5px 10px 5px">'+text+
					'</div><div style="margin-top: 5px; color: #555555">Default: '+deflt+'</div>').css({
						display: 'block'
					}).appendTo('body');
					winW = parseInt($(window).width());
					winH = parseInt($(window).height());
					scrollTop = parseInt($(window).scrollTop());
					height = toolTip.outerHeight();
					width = toolTip.outerWidth();
					setPos(e);
				}
			}).bind('mousemove', function(e){
				setPos(e);
			}).bind('touchend mouseleave', function(e){
				$('.axZmHoverTooltip').remove();
			}).css({cursor: 'help'});
		}
		return this;
    };
 
	
	$(document).bind("ready", function(){
		
		// init tabs for editor
		$('#aZhS_tabs, #aZhS_about, #aZhS_hotspots, #aZhS_tooltip, #aZhS_tooltips, #aZhS_save, #aZhS_events').tabs();
		
		$('.optDescr').filter(function() {
			var txt = $(this).text(),
				deflt = $('#hsOpt_'+txt).next().text(),
				descr = $('#hsOpt_'+txt).next().next().text().replace('<', '&lt;');
			$(this).data('optDescr', descr)
			.data('optDeflt', deflt)
			.azSimpleToolTip()
			.css({fontStyle: 'italic'});
		});
		
		if ($.isFunction($.aZhSpotEd.saveHotspotJS)){
			$.aZhSpotEd.saveHotspotJS();
			$('#allHotspotsCode').val('');
			setTimeout(function(){
				$('a[href$="#aZhS_tabs-5"], .linkShowJson').bind('click', function(){
					if (!$('#allHotspotsCode').val()){
						importJSON();
					}
				});
			}, 2000);
		}
		
		$(document).delegate('textarea', 'keydown', function(e) {
			var keyCode = e.keyCode || e.which;
			if (keyCode == 9) {
				e.preventDefault();
				var start = $(this).get(0).selectionStart;
				var end = $(this).get(0).selectionEnd;
				$(this).val($(this).val().substring(0, start)+"\t"+$(this).val().substring(end));
				$(this).get(0).selectionStart = $(this).get(0).selectionEnd = start + 1;
			}else{
				if ($(this).attr('id') == 'allHotspotsCode'){
					$('#applyJSON').css('backgroundColor', 'red');
				}
			}
		});
		
		$(document).delegate('input, textarea', 'keydown', function(e) {
			e.stopPropagation();
		});

		$(window).on('beforeunload.azHotspotEditor', function(e){
			if ($('#saveWarningImg').length){
			    var confirmationMessage = "It looks like you have been editing something.\r\n";
			    confirmationMessage += "If you leave before saving, your changes will be lost.";

			    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
			    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
			}
		});
		
	});

})(jQuery);