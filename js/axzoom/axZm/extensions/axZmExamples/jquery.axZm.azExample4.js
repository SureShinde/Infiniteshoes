/*!
* Plugin: jQuery AJAX-ZOOM, jquery.axZm.azExample4.js
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Extension Version: 1.1
* Extension Date: 2016-03-07
* URL: http://www.ajax-zoom.com
* Documentation: http://www.ajax-zoom.com/index.php?cid=docs
* Example: http://www.ajax-zoom.com/examples/example4.php
*/


;(function($){
	
	$.azExample4 = function(opt){
		
		var def = {
				axZmPath: "auto", // Path to /axZm directory, e.g. /test/axZm/
				zoomDir: "", // Path to subfolders with images
				divID: "axZmPlayerContainer", // ID of the main container
				menuDivID: "lavaLampContainer", // ID of the menu container
				firstFolder: 1, // index or name of the folder to be loaded at first
				firstImage: 1, // index or name of the image to load from firstFolder
				example: 8, // configuration set value which is passed to ajax-zoom
				axZmCallBacks: {}, // AJAX-ZOOM has several callbacks, http://www.ajax-zoom.com/index.php?cid=docs#onBeforeStart
				responsive: false, // Open responsive
				fullScreenApi: false // try to open AJAX-ZOOM at browsers fullscreen mode
			};
			
		var op = $.extend({}, def, opt, true);
		
		var submitNewZoom = function(menuItem){
			var folder = $(menuItem).attr('data-folder');
			if (folder){
				var data = 'example='+op.example+'&zoomDir='+folder;
				$.fn.axZm.loadAjaxSet(data);
			}	
		};

		var loadDirs = function(){
			var url = op.axZmPath+'zoomLoad.php',
				urlData = 'zoomDir='+op.zoomDir+'&qq=folders';
			
			$.ajax({
				url: url,
				data: urlData,
				cache: false,
				dataType: 'JSON',
				success: function (data){
					if ($.isArray(data) && data[0] != 'error'){
						var folderToLoad = data[0] + data[1][1]['folderName'];
						
						var ul = $('<ul />')
							.attr('id','lavalampMenu')
							.addClass('lavaLampNoImageZoom')
							
						$.each(data[1], function(k, v){
							var a = $('<a />')
								.text(v.folderName)
								.css('cursor', 'pointer')
								.attr('href', '#')
							
							var li = $('<li />')
								.attr('id', 'zoomSet'+k)
								.attr('data-folder', v.folderName)
								.append(a)
								
							
							if ( v.folderName == op.firstFolder || parseInt(k) == parseInt(op.firstFolder) || (data[0]+v.folderName) == op.firstFolder){
								li.addClass('current');
								folderToLoad = data[0]+v.folderName;
							}
							
							li.appendTo(ul);
							
						});
						
						$('#'+op.menuDivID).empty().append(ul);
						
						// Init menu under the player
						$("#lavalampMenu").lavaLamp({
							fx: "easeOutBack",
							speed: 750,
							click: function(event, menuItem) {
								submitNewZoom(menuItem);
								return false;
							}
						});	
						
						var parampeterToPass = 'zoomDir='+folderToLoad+'&example='+op.example;
						
						if (parseInt(op.firstImage) == op.firstImage){
							parampeterToPass += '&zoomID='+op.firstImage;
						}else{
							parampeterToPass += '&zoomFile='+op.firstImage;
						}
						

						
						if (op.responsive){
							$.fn.axZm.openFullScreen(
								op.axZmPath, 
								parampeterToPass, 
								op.aZcallBacks, 
								op.divID, 
								op.fullScreenApi, 
								true);
						}else{
							$.fn.axZm.load({
								opt: op.aZcallBacks,
								path: op.axZmPath,
								parameter: parampeterToPass,
								divID: op.divID,
								apiFullscreen: op.fullScreenApi
							}); 
						}
					
					}
					else{
						// Some error handling
						var errText = 'Error: failed to load folders for gallery';
						if ($.isArray(data) && data[0] == 'error'){
							errText += '<br>'+data[1]+': '+zoomDir;
						}
						
						$('#'+op.divID)
						.html('<div style="color: red; padding: 5px; background-color: #FFF; border: 1px solid red; background-color: #EEE">'+errText+'</div>');
						
						$('#'+op.menuDivID)
						.html('<div style="color: red; padding: 5px;">Error</div>');
					}
				},
				error: function(jqXHR, textStatus, errorThrown ){
					// Some error handling
					var errText = 'Error '+jqXHR.status+' ('+errorThrown+')<br>';
						errText += 'Failed to load folders for gallery with the following request: <br>';
						errText += url+'?'+urlData;
					
					$('#'+op.divID)
					.html('<div style="color: red; padding: 5px; background-color: #FFF; border: 1px solid red; background-color: #EEE">'+errText+'</div>');
					
					$('#'+op.menuDivID)
					.html('<div style="color: red; padding: 5px;">Error</div>');
				}
			});
			
		};
		
		var init = function(){

			if (!op.axZmPath || op.axZmPath == 'auto'){
				if ($.isFunction($.fn.axZm)){
					op.axZmPath = $.fn.axZm.installPath();
				}else{
					alert('jquery.axZm.js is not loaded');
					return;
				}
			}
			
			if (!$('#'+op.divID).length){
				alert('Container with ID '+op.divID+' was not found.');
				return;
			}
			
			if (!$('#'+op.menuDivID).length){
				alert('Container with ID '+op.menuDivID+' was not found.');
				return;
			}
			
			if ($.axZm){
				$.fn.axZm.spinStop();
				$.fn.axZm.remove();
				$('#axZmTempBody').axZmRemove(true);
				$('#axZmTempLoading').axZmRemove(true);
				$('#axZmWrap').axZmRemove(true);
			}

			loadDirs();
		};
		
		// If $.azExample4 was inited before needed DOM is ready
		if (!$('#'+op.divID).length){
			$(document).ready(init);
		}else{
			init();
		}
	};
	
})(jQuery);