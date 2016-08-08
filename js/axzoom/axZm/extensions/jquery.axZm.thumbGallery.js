/*!
* Plugin: jQuery AJAX-ZOOM, jquery.axZm.thumbGallery.js
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Extension Version: 1.2
* Extension Date: 2016-03-07
* URL: http://www.ajax-zoom.com
* Documentation: http://www.ajax-zoom.com/index.php?cid=docs
* Example: http://www.ajax-zoom.com/examples/example21.php
*/

/**
* Hello developer!
* If you are about to modify this file, which is ok, then please upload a renamed copy of it to the same folder and reference to this modified copy in your frontend!
* Otherwise your hard work could get lost when your colleague will upgrade AJAX-ZOOM in the future, which is probably not ok then :)
*/

;(function($){

	$.azThumbGallery = function(opt){

	 	var def = {
			axZmPath: "auto", // Path to /axZm directory, e.g. /test/axZm/
			// The look of navigation for subfolders. Only useable if "zoomDir" is defined (not with "zoomData");
			// Possible values: "select", "folders", "imgFolders" or false
			folderSelect: 'select',
			zoomDir: null, // Path to subfolders with images
			firstFolder: null, // After page loads select from which subfolder thumbnails should be loaded first, integer (index of subfolder) or string (name of the subfolder)
			zoomData: null,

			// Applied if folderSelect is set to "folders"
			// Prefix of icon image located in /axZm/icons directory
			// There are three of them on default: folder_icon_close.png, folder_icon_close_over.png, folder_icon_open.png
			folderIconPrefix: "folder_icon_",

			// Settings for small icon images when folderSelect option is set to "imgFolders"
			// In the css file you can change the appearance by editing .azImgFolder *
			imgFoldersSettings: {
				thumbNumber: 3, // amount of icons to show, max 3
				thumbWidth: 32, // width
				thumbHeight: 32, // height
				thumbRetina: true,
				thumbQual: 100,
				thumbBackColor: '#FFFFFF',
				thumbMode: 'contain',
				thumbsCache: true,
				thumbOpacity: 1
			},

			folderNameFunc: function(input){
				input = (input.charAt(0).toUpperCase() + input.slice(1)).replace('_', ' ');

				if (input.length > 15){
					input = input.substring(0, 15)+'...';
				}
				return input;
			},

			// AJAX-ZOOM has several callbacks,
			// Docu: http://www.ajax-zoom.com/index.php?cid=docs#onBeforeStart
			axZmCallBacks: {},
			fullScreenApi: false, // try to open AJAX-ZOOM at browsers fullscreen mode
			setHash: false,

			thumbsCache: true, // cache thumbnails
			thumbWidth: 120, // width of the thumbnail image
			thumbHeight: 120, // height of the thumbnail image
			thumbRetina: true, // true will double the resolution of the thumbnails images but keep thumbWidth and thumbHeight on screen
			thumbQual: 90, // jpg quality of the thumbnail image
			thumbMode: false, // possible values: "contain", "cover" or false
			thumbBackColor: "#FFFFFF", // background color of the thumb if thumbMode is set to "contain"
			thumbsPerPage: null, // Show this number of thumbnails at once
			thumbPadding: null, // Padding
			thumbMarginRight: null,
			thumbMarginBottom: null,
			thumbCss: {}, // set css besides css file

			thumbDescr: [], //  array; poaaible array elements: fileName, thumbDescr, fullDescr;
			thumbDescrJoin: '<br>', // if thumbDescr has more than one elemets, they will be splitted by this string
			thumbDescrTruncate: false, // integer - truncate each of the descriptions or false

			thumbPreloadingImg: "ajax-loader-map-white.gif", // image located in /axZm/icons folder which is shown befor thumbnail is loaded

			thumbsContainer: "thumbsParentContainer", // ID of the element where thumbnails appended to
			selectContainer: "selectParentContainer", // ID of the element where the select with subfolders will be appended to


			// possible values: "fullscreen", "fancyboxFullscreen", "fancybox", "colorbox", "zoomSwitch"
			// zoomSwitch is only possible when player is ebmedded
			ajaxZoomOpenMode: "fullscreen",
			exampleFullscreen: "mouseOverExtension", // configuration set value which is passed to ajax-zoom when ajaxZoomOpenMode is "fullscreen"
			exampleFancyboxFullscreen: "mouseOverExtension", // configuration set value which is passed to ajax-zoom when ajaxZoomOpenMode is "fancyboxFullscreen"
			exampleFancybox: "modal", // configuration set value which is passed to ajax-zoom when ajaxZoomOpenMode is "fancybox"
			exampleColorbox: "modal", // configuration set value which is passed to ajax-zoom when ajaxZoomOpenMode is "colorbox"

			// If fancybox is used in "ajaxZoomOpenMode" option, below are some fancybox options
			fancyBoxParam: {
				boxMargin: 0,
				boxPadding: 10,
				boxCenterOnScroll: true,
				boxOverlayShow: true,
				boxOverlayOpacity: 0.75,
				boxOverlayColor: "#777",
				boxTransitionIn: "fade", // "elastic", "fade" or "none"
				boxTransitionOut: "fade", // "elastic", "fade" or "none"
				boxSpeedIn: 300,
				boxSpeedOut: 300,
				boxEasingIn: "swing",
				boxEasingOut: "swing",
				boxShowCloseButton: true, // close button
				boxEnableEscapeButton: true,
				boxOnComplete: function(){},
				boxTitleShow : false,
				boxTitlePosition : "float", // "float", "outside", "inside" or "over"
				boxTitleFormat : null
			},

			// If colorbox is used in "ajaxZoomOpenMode" option, below are some Colorbox options
			colorBoxParam: {
				transition: "elastic",
				speed: 300,
				scrolling: true,
				title: true,
				opacity: 0.9,
				className: false,
				current: "image {current} of {total}",
				previous: "previous",
				next: "next",
				close: "close",
				onOpen: false,
				onLoad: false,
				onComplete: false,
				onClosed: false,
				overlayClose: true,
				escKey: true
			},


			embedMode: false, // AJAX-ZOOM is embedded into some container (embedDivID) next to gallery thumbs
			embedDivID: "", // The ID of the element (placeholder) where AJAX-ZOOM has to be inserted into
			embedResponsive: false, // set this to true if embedDivID is a responsive container
			embedExample: 9, // example value passed to AJAX-ZOOM when embedMode is activated
			// When clicked on the thumbs the image inside AJAX-ZOOM will be switched with one of the following effects:
			// Possible values: "Center", "Top", "Right", "Bottom", "Left", "StretchVert", "StretchHorz", "SwipeHorz", "SwipeVert", "Vert", "Horz"
			embedZoomSwitchAnm: 'SwipeHorz',
			embedZoomSwitchSpeed: 300, // set speed of switching, - $.axZm.galleryFadeInSpeed, $.axZm.galleryInnerFade, $.axZm.gallerySlideSwipeSpeed
			embedSwitchWithPage: true, // If "thumbsPerPage" is activated and page numbers are present, then clicking on the page number will switch to the first shown image on that page.
			// When gallery loads first the index (number) or file name which should be loaded first.
			// See also "firstFolder" option in case applied.
			embedFirstImage: 1
		};

		// Helper function
	    var isObject = function(x) {
	        return (typeof x === 'object') && !(x instanceof Array) && (x !== null);
	    };

		// Helper function
		var objLength = function(a){
			//return Object.keys(a).length;
		    var i=0; $.each(a, function(k, v){ i++; });
		    return i;
		};

		// Helper function
		var getParameterByName = function(name) {
		    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		        results = regex.exec(location.search);

		    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		};

	    // Local vars
		var op = $.extend(true, {}, def, opt),
			currentData = {},
			currentPage = 1,
			currentLoadParam = null,
			hashChangeBlock = false,
			folderList = {},
			sel,
			obj;

		// Convert zoomData option from array or object to string (CSV separated with |)
		var zoomDataCheck = function(str){
			if (typeof str == 'string'){
				return str;
			}else if ($.isArray(str)){
				return str.join('|');
			}else if (isObject(str)){
				var arr = [];
				$.each(str, function(k, v){
					if (typeof v == 'string'){
						arr.push(v);
					}
				});
				return arr.join('|');
			}else{
				return null;
			}
		};

		// Observe hash value and change folder if it changes
		var observeHash = function(){
			if (!hashChangeBlock){
				var h = window.location.hash.replace('#','');
				if (!h){
					for (var prop in folderList){h = prop; break;}
				}

				if (folderList[h]){
					loadImageThumbs('zoomDir', folderList[h]);
					if (sel.length){
						if (op.folderSelect == 'select'){
							sel.val(folderList[h]);
						} else if (op.folderSelect == 'folders'){
							$('.folder', sel)
							.attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'close.png')
							.removeClass('selected');

							$('.descr', sel).removeClass('selected');

							$('.folder[data-folderName="'+h+'"]', sel)
							.attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'open.png')
							.addClass('selected');

							$('.folder[data-folderName="'+h+'"]', sel).parent().find('.descr').addClass('selected');


						} else if (op.folderSelect == 'imgFolders'){
							$('img.icon', sel).removeClass('selected');

							$('li[data-folderName="'+h+'"] img.icon', sel).addClass('selected');

							$('div.descr', sel).removeClass('selected');
							$('li[data-folderName="'+h+'"] div.descr', sel).addClass('selected');
						}
					}
				}
			}
		};


		var trunc = function(a){
			var len = op.thumbDescrTruncate,
				aLen = a.length;


			if (len > 3 && aLen > len){
				a = a.substr(0, len - 3);
				return a+'...';
			}else{
				return a;
			}
		};

		// Draw thumbs
		var displayImageThumbs = function(data, page, noAfterSwitch){
			if ($.isArray(data)){
				if (data[0] == 'error'){
					// Some error handling
					var errText = 'Failed to load thumbs';
					if ($.isArray(data) && data[0] == 'error'){
						errText += '<br>'+data[1];
					}

					obj.html('<div style="color: red; padding: 5px;">'+errText+'</div>');
				}else{
					var dataLen = objLength(data[1]),
						zoomDir = data[0],
						preloadObj = {},
						firstImage = null;

					if (!page){page = 1;}
					currentPage = page;

					// Thumbs per page calculation
					var noPages = false, from = false, to = false;

					op.thumbsPerPage = parseInt(op.thumbsPerPage);
					if (op.thumbsPerPage < 1){op.thumbsPerPage = null;}

					if (op.thumbsPerPage && op.thumbsPerPage < dataLen){
						noPages = Math.ceil(dataLen/op.thumbsPerPage);
						from = op.thumbsPerPage * (page-1) +1;
						to = from - 1 + op.thumbsPerPage;
					}

					var liCss = {
						width: op.thumbWidth,
						height: op.thumbHeight
					};

					if (op.thumbPadding > 0){liCss.padding = op.thumbPadding;}
					if (op.thumbMarginRight > 0){liCss.marginRight = op.thumbMarginRight;}
					if (op.thumbMarginBottom > 0){liCss.marginBottom = op.thumbMarginBottom;}

					var ul = $('<ul />')
					 	.addClass('azThumb');

					$.each(data[1], function(k, v){


						// Skip thumbs which are not at the page number
						if (to > 0){
							if (k < from || k > to){
								return;
							}
						}

						if (!firstImage){
							firstImage = v['fileName'];
						}

						var dynThumbPath =
						op.axZmPath+'zoomLoad.php?'
						+'previewDir='+(v.picPath || data[0])
						+'&previewPic='+v.fileName
						+'&qual='+op.thumbQual
						+'&width='+(op.thumbRetina ? op.thumbWidth*2 : op.thumbWidth)
						+'&height='+(op.thumbRetina ? op.thumbHeight*2 : op.thumbHeight)
						+'&cache='+op.thumbsCache
						+'&thumbMode='+op.thumbMode
						+'&backColor='+op.thumbBackColor.replace('#', '%23')
						;


						// "Empty" li ellement
						var li = $('<li />')
							.attr('data-path', (v.picPath || data[0]))
							.attr('data-img', v.fileName)
							.css(liCss)
							.css(op.thumbCss)
							.bind('click', function(){
								var fileName = v['fileName'],
									zoomDir = data[0],
									zoomID = k,
									// Dummy function
									onBoxesClose = function(){

									};

								// Open AJAX-ZOOM as fullscreen
								if (op.ajaxZoomOpenMode == 'fullscreen'){
									var aZcallBacks = $.extend(true, {}, op.axZmCallBacks);
									$.fn.axZm.openFullScreen(
										op.axZmPath,
										'zoomFile='+fileName+(op.zoomData ? '&zoomData='+op.zoomData : '&zoomDir='+zoomDir)+'&example='+op.exampleFullscreen,
										aZcallBacks,
										'window',
										op.fullScreenApi,
										false
									);
								}

								// Open AJAX_ZOOM as modified / responsive Fancybox
								else if (op.ajaxZoomOpenMode == 'fancyboxFullscreen'){

									if (!$.isFunction($.openAjaxZoomInFancyBox)){
										alert('Please include following scripts in the head section:\n\n/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.css \n/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.pack.js \n/axZm/extensions/jquery.axZm.openAjaxZoomInFancyBox.js \n\nImportant: it has to be adjusted Fancybox from AJAX-ZOOM package!\n');
										return false;
									}

									if (op.fancyBoxParam.boxMargin < 30){
										op.fancyBoxParam.boxMargin = 30;
									}

									var aZcallBacks = $.extend(true, {}, op.axZmCallBacks);

									var thisParam = {
										axZmPath: op.axZmPath,
										queryString: 'example='+op.exampleFancyboxFullscreen+'&zoomFile='+fileName+(op.zoomData ? '&zoomData='+op.zoomData : '&zoomDir='+zoomDir),
										fullScreenApi: op.fullScreenApi,
										ajaxZoomCallbacks: aZcallBacks,
										boxOnClosed: onBoxesClose
									};

									$.openAjaxZoomInFancyBox($.extend(true, {}, thisParam, aZcallBacks));
								}

								// Open AJAX_ZOOM in regular Fancybox
								else if (op.ajaxZoomOpenMode == 'fancybox'){

									$('#axZmTempBody, #axZmWrap').axZmRemove(true);
									var axZmWrap = $('<div />').css({display: 'none'}).attr('id', 'axZmWrap').appendTo('body');

									// Trigger fancybox
									var onStart = function(){
										axZmWrap.css('display','');

										// fancyBoxParam
										var thisParam = {
											showNavArrows: false,
											enableKeyboardNav: false,
											hideOnContentClick: false,
											scrolling: 'no',
											width: 'auto',
											height: 'auto',
											autoScale: false,
											autoDimensions: true,
											href: '#axZmWrap',
											title: op.fancyBoxParam.boxTitleShow ? (currentData[1][$.axZm.zoomID]['fullDescr'] || 'Image No. '+$.axZm.zoomID) : null,
											onClosed: function(){
												onBoxesClose();
												$.fn.axZm.spinStop();
												$.fn.axZm.remove();
												$('#axZmTempBody').axZmRemove(true);
												$('#axZmTempLoading').axZmRemove(true);
												$('#axZmWrap').axZmRemove(true);
											},
											beforeClose: function(){ // fancybox 2.x
												onBoxesClose();
												$.fn.axZm.spinStop();
												$.fn.axZm.remove();
												$('#axZmTempBody').axZmRemove(true);
												$('#axZmTempLoading').axZmRemove(true);
												$('#axZmWrap').axZmRemove(true);
											}
										};

										var fancyBoxParam = {};

										$.each(op.fancyBoxParam, function(k, v){
											k = k.substr(3);
											fancyBoxParam[k.charAt(0).toLowerCase() + k.slice(1)] = v;
										});

										$.fancybox($.extend(true, {}, fancyBoxParam, thisParam));
									};

									// Change title
									var onImageChange = function(){
										if (op.fancyBoxParam.boxTitleShow){
											if ($.fancybox.init){
												var titleDivMap = {
													'float': 'fancybox-title-float-main',
													'outside': 'fancybox-title-outside',
													'inside': 'fancybox-title-inside',
													'over': 'fancybox-title-over'
												}

												$('#'+titleDivMap[op.fancyBoxParam.boxTitlePosition]).html(currentData[1][$.axZm.zoomID]['fullDescr'] || ('Image No. '+$.axZm.zoomID + ' / ' + $.axZm.numGA));

												if (op.fancyBoxParam.boxTitlePosition == 'float'){
													$('#fancybox-title').css('left', $('#fancybox-wrap').outerWidth()/2 - $('#fancybox-title').outerWidth()/2);
												}
											}else{
												var ourTitleDiv = $('.fancybox-title');
												var ourTitle = currentData[1][$.axZm.zoomID]['fullDescr'] || ('Image No. '+$.axZm.zoomID + ' / ' + $.axZm.numGA);
												if (ourTitleDiv.length){
													if (ourTitleDiv.children().first().length){
														ourTitleDiv.children().first().html(ourTitle);
													}else{
														ourTitleDiv.html(ourTitle);
													}
												}
											}
										}
									};

									var aZcallBacks = $.fn.axZm.mergeCallBackObj({onStart: onStart, onImageChange: onImageChange}, op.axZmCallBacks);

									$.fn.axZm.load({
										opt: aZcallBacks,
										path: op.axZmPath,
										parameter: 'zoomFile='+fileName+(op.zoomData ? '&zoomData='+op.zoomData : '&zoomDir='+zoomDir)+'&example='+op.exampleFancybox,
										divID: 'axZmWrap',
										apiFullscreen: op.fullScreenApi
									});

								}

								// Open AJAX_ZOOM in Colorbox
								else if (op.ajaxZoomOpenMode == 'colorbox'){

									$('#axZmTempBody, #axZmWrap').axZmRemove(true);
									var axZmWrap = $('<div />').css({display: 'none'}).attr('id', 'axZmWrap').appendTo('body');

									var onStart = function(){
										axZmWrap.css('display','');

										var thisParam = {
											opacity: 0.9,
											initialWidth: 300,
											initialHeight: 300,
											preloading: false,
											scrolling: false,
											scrollbars: false,
											title: op.colorBoxParam.title ? currentData[1][$.axZm.zoomID]['fullDescr'] : false,
											onCleanup: function(){
												onBoxesClose();
												$.fn.axZm.spinStop();
												$.fn.axZm.remove();
												$('#axZmTempBody').axZmRemove(true);
												$('#axZmTempLoading').axZmRemove(true);
												$('#axZmWrap').axZmRemove(true);
											},
											inline: true,
											href: '#axZmWrap',
											ajax: false
										};

										$.colorbox($.extend(true, {}, op.colorBoxParam, thisParam));

									};

									var onImageChange = function(){
										if (op.colorBoxParam.title){
											if (currentData[1][$.axZm.zoomID]['fullDescr']){
												$('#cboxTitle').html(currentData[1][$.axZm.zoomID]['fullDescr']);
											}else{
												$('#cboxTitle').html('');
											}
										}
									};

									var aZcallBacks = $.fn.axZm.mergeCallBackObj({onStart: onStart, onImageChange: onImageChange}, op.axZmCallBacks);

									$.fn.axZm.load({
										opt: aZcallBacks,
										path: op.axZmPath,
										parameter: 'zoomFile='+fileName+(op.zoomData ? '&zoomData='+op.zoomData : '&zoomDir='+zoomDir)+'&example='+op.exampleColorbox,
										divID: 'axZmWrap',
										apiFullscreen: op.fullScreenApi
									});
								}

								// In case AJAX-ZOOM is embeded, just switch to the image
								else if (op.ajaxZoomOpenMode == 'zoomSwitch'){
									$('li', obj).removeClass('selected');
									$(this).addClass('selected');
									$.fn.axZm.zoomSwitch(fileName, op.embedZoomSwitchAnm);
								}

								// Custom
								else if ($.isFunction(op.ajaxZoomOpenMode)){
									if (op.data.axZmCallbacks){
										$.extend(op.data.axZmCallbacks, op.axZmCallBacks);
									}else{
										op.data.axZmCallbacks = $.extend(true, {}, op.axZmCallBacks);
									}

									op.data.zoomID = zoomID;
									op.ajaxZoomOpenMode(op.data);
								} else {
									alert('Sorry, but at this point there are no other mods than (AJAX-ZOOM) "fullscreen", "fancyboxFullscreen", "fancybox" and "colorbox".');
								}

							});

						// Append "preloading" image to the li
						var img = $('<img>')
							.addClass('thumb')
							.attr('src', op.axZmPath+'icons/'+op.thumbPreloadingImg)
							.attr('id', 'thumbFullscreen_'+k)
							.attr('data-path', (v.picPath || data[0]))
							.appendTo(li);



						// For vertical center...
						var span = $('<span />').text(' ').addClass('vAlign').appendTo(li);

						// Description
						if ($.isArray(op.thumbDescr)){
							var txt = [];
							$.each(op.thumbDescr, function(ii, vv){
								if (v[vv]){

									txt.push(trunc(v[vv]))
								}
							});

							$('<div />').html(txt.join('<br>')).addClass('descr').appendTo(li);
						}


						// Append li to ul
						li.appendTo(ul);

						// Preload actual thumb
						preloadObj[k] = function(){
							$('<img>').load(function(){
								// Replace "preloading" image with the thumb
								$('#thumbFullscreen_'+k)
								.attr('src', dynThumbPath)
								.removeAttr('width') // IE
								.removeAttr('height'); // IE
							}).attr('src', dynThumbPath);
						};
					});

					var pageContainer = null;

					// Add page numbers navigation
					if (noPages > 1){

						var pageContainer = $('<div />')
						.addClass('axPagesParent');

						for (var i = 1; i <= noPages; i++) {

							var azPages = $('<div />')
							.addClass('azPages'+(currentPage == i ? ' selected' : ''))
							.text(i)
							.data('pageNo', i)
							.bind('click', function(){
								displayImageThumbs(currentData, $(this).data('pageNo'))
							})
							.appendTo(pageContainer);
						}
					}

 					obj.empty().prepend(ul);
					if (pageContainer){
						obj.append(pageContainer);
					}

					// Fire AJAX-ZOOM if it is embedded
					if (op.embedMode){
						var loadParam = (op.zoomData ? '&zoomData='+op.zoomData : '&zoomDir='+zoomDir)+'&example='+op.embedExample,
							embedFirstImage = '';

						if (!currentLoadParam && op.embedFirstImage){
							embedFirstImage = '&'+(parseInt(op.embedFirstImage) == op.embedFirstImage ? 'zoomID='+op.embedFirstImage : 'zoomFile='+op.embedFirstImage);
						}

						if (currentLoadParam && $.axZm){
							var cImg = $.axZm.zoomGA[$.axZm.zoomID].img;
							$('li', obj).removeClass('selected');
							$('li[data-img="'+cImg+'"]', obj).addClass('selected');
						}

						if (currentLoadParam != loadParam){

							if (!currentLoadParam){
								$.fn.axZm.spinStop();
								$.fn.axZm.remove();
								$('#axZmTempBody').axZmRemove(true);
								$('#axZmTempLoading').axZmRemove(true);
								$('#axZmWrap').axZmRemove(true);


								// Merge callbacks

								op.axZmCallBacks = $.fn.axZm.mergeCallBackObj(
								op.axZmCallBacks, // callbacks passed over plugin
								// some internal callbacks for the gallery
								{
									onBeforeStart: function(){
										if (op.embedZoomSwitchSpeed){
											$.axZm.galleryFadeInSpeed = op.embedZoomSwitchSpeed;
											$.axZm.galleryInnerFade = op.embedZoomSwitchSpeed;
											$.axZm.gallerySlideSwipeSpeed = op.embedZoomSwitchSpeed;
										}

										if (op.embedZoomSwitchAnm){
											$.axZm.galleryFadeInAnm = op.zoomSwitchAnm;
										}
									},
									onLoad: function(){
										// Heilight selected thumb
										var cImg = $.axZm.zoomGA[$.axZm.zoomID].img;
										$('li[data-img="'+cImg+'"]', obj).addClass('selected');
									},
									onImageChangeEnd: function(){
										// Heilight selected thumb

										var cImg = $.axZm.zoomGA[$.axZm.zoomID].img;
										var foundThumb = $('li[data-img="'+cImg+'"]', obj);
										if (foundThumb.length > 0){
											$('li', obj).removeClass('selected');
											$('li[data-img="'+cImg+'"]', obj).addClass('selected');
										}else if (op.embedSwitchWithPage && noPages > 1){
											// Switch pages too if image change has been inited by API
											displayImageThumbs(currentData, Math.ceil($.axZm.zoomID/op.thumbsPerPage), true);
										}
									}
								});


								// AJAX-ZOOM parent container is responsive
								if (op.embedResponsive){
									$.fn.axZm.openFullScreen(
										op.axZmPath,
										loadParam+embedFirstImage,
										op.axZmCallBacks,
										op.embedDivID,
										op.fullScreenApi,
										true
									);
								}
								// Parent container has fixed width and height
								else{
									$.fn.axZm.load({
										opt: op.axZmCallBacks,
										path: op.axZmPath,
										parameter: loadParam+embedFirstImage,
										divID: op.embedDivID,
										apiFullscreen: op.fullScreenApi
									});
								}

								$('li', obj).removeClass('selected');

							}

							else{
								$.fn.axZm.loadAjaxSet(loadParam, function(){
									// Heilight selected thumb
									var cImg = $.axZm.zoomGA[$.axZm.zoomID].img;
									$('li', obj).removeClass('selected');
									$('li[data-img="'+cImg+'"]', obj).addClass('selected');
								});
							}


						}

						if (op.embedSwitchWithPage && currentLoadParam == loadParam && !noAfterSwitch){
							$.fn.axZm.zoomSwitch(firstImage, op.embedZoomSwitchAnm);
						}

						currentLoadParam = loadParam;
					}

					// Start preloading thumbs after
					$.each(preloadObj, function(k, v){v();});
					//
				}
			}
			else{
				// Error handling
				var errText = 'Failed to load thumbs';
				errText += '<br>Data passed to the displayImageThumbs function is not an array!';
				obj.html('<div style="color: red; padding: 5px;">'+errText+'</div>');
			}
		};

		// Get images from certain folder and display thumbs on success
		var loadImageThumbs = function(prop, val){
			hashChangeBlock = true;
			var urlLoad = op.axZmPath+'zoomLoad.php',
				dataToPass = prop+'='+val+'&qq=images';

			$.ajax({
				url: urlLoad,
				data: dataToPass,
				cache: false,
				dataType: 'JSON',
				success: function (data){
					// Save data
					currentData = data;

					displayImageThumbs(data);

					hashChangeBlock = false;
				},
				error: function(jqXHR, textStatus, errorThrown){
					hashChangeBlock = false;

					// Error handling
					var errText = 'Failed to load thumbs';
					errText += '<br>Error thrown: '+jqXHR.status+' '+jqXHR.statusText;
					errText += '<br>Requested URL: '+urlLoad+'?'+dataToPass;
					obj.html('<div style="color: red; padding: 5px;">'+errText+'</div>');
				}
			});
		};

		// Draw folders
		var folderSelect = function(customDir){
			var urlLoad = op.axZmPath+'zoomLoad.php',
				setFromHash = false,
				dataToPass = 'zoomDir='+(customDir || op.zoomDir)+'&qq='+(op.folderSelect == 'imgFolders' ? 'firstImagesFromFolders' : 'folders');
				if (op.folderSelect == 'imgFolders' ){
					dataToPass += '&imgNumber='+op.imgFoldersSettings.thumbNumber;
				}
			$.fn.axZm.stopPlay();
			$.ajax({
				url: urlLoad,
				data: dataToPass,
				cache: false,
				dataType: 'JSON',
				success: function (data){
					if ($.isArray(data)){
						if (data[0] == 'error'){
							var errText = 'Failed to load subfolders';
							errText += '<br>'+data[1];
							obj.html('<div style="color: red; padding: 5px;">'+errText+'</div>');
						}else{
							$.fn.axZm.stopPlay();
							folderList = {};
							var firstFolderToLoad = null,
								firstFolderNo = 1,
								firstFolderName = null;

							$.each(data[1], function(k, v){
								if (k == 1){
									firstFolderToLoad = data[0]+v.folderName;
									firstFolderName = v.folderName;
								}
								folderList[v.folderName] = data[0]+v.folderName;

								if (op.firstFolder == v.folderName || op.firstFolder == k || parseInt(op.firstFolder) == k){
									firstFolderToLoad = data[0]+v.folderName;
									firstFolderName = v.folderName;
								}
							});

							if (op.setHash){
								var h = window.location.hash.replace('#','');
								if (folderList[h]){
									firstFolderToLoad = folderList[h];
									firstFolderName = h;
									setFromHash = firstFolderToLoad;
								}
							}

							if (op.folderSelect == 'select'){
								sel = $('<select />');
							} else if (op.folderSelect == 'folders'){
								sel = $('<ul />').addClass('azFolder');
							} else if (op.folderSelect == 'imgFolders'){
								sel = $('<ul />').addClass('azImgFolder');
							} else {
								return false;
							}

							$.each(data[1], function(k, v){

								if (op.folderSelect == 'select'){
									var newOpt = $('<option />')
									.attr('value', data[0]+v.folderName)
									.text(op.folderNameFunc(v.folderName))
									.appendTo(sel);
								}

								else if (op.folderSelect == 'folders'){
									var newOpt = $('<li />')
										.data('path', data[0]+v.folderName)
										.data('folderName', v.folderName)
										.attr('data-folderName', v.folderName)
										.bind('click', function(){
											if (op.setHash){
												hashChangeBlock = true;
												window.location.hash = $(this).data('path').split('/').reverse()[0];
											}

											$('.folder', sel)
											.attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'close.png')
											.removeClass('selected');

											$('.descr', sel).removeClass('selected');


											$('.folder',$(this))
											.attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'open.png')
											.addClass('selected');

											$('.descr', $(this)).addClass('selected');


											loadImageThumbs('zoomDir', $(this).data('path'));
										})
										.bind('mouseover touchstart', function(){
											if (!$('.folder', $(this)).hasClass('selected')){
												$('.folder', $(this)).attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'close_over.png')
											}
										})
										.bind('mouseout touchend', function(){
											if (!$('.folder', $(this)).hasClass('selected')){
												$('.folder', $(this)).attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'close.png')
											}
										});

									var folderIcon = $('<img>')
									.attr('data-folderName', v.folderName)
									.attr('src', op.axZmPath+'icons/'+op.folderIconPrefix + 'close.png')
									.addClass('folder')
									.appendTo(newOpt);

									$('<span />').text(' ').addClass('vAlign').appendTo(newOpt);
									$('<div />').html(op.folderNameFunc(v.folderName)).addClass('descr').appendTo(newOpt);
									newOpt.appendTo(sel);
								}

								else if (op.folderSelect == 'imgFolders'){
									var newOpt = $('<li />')
										.data('path', data[0]+v.folderName)
										.data('folderName', v.folderName)
										.attr('data-folderName', v.folderName)
										.bind('click', function(e){

											if (op.setHash){
												hashChangeBlock = true;
												window.location.hash = $(this).data('path').split('/').reverse()[0];
											}
											$('img.icon', sel).removeClass('selected')
											.trigger('mouseout')
											.trigger('touchend');

											$('img.icon', $(this)).addClass('selected');
											loadImageThumbs('zoomDir', $(this).data('path'));

											$('div.descr', sel).removeClass('selected');
											$('div.descr', $(this)).addClass('selected');
										})
										.bind('mouseover touchstart', function(e){

											if (!$('.icon', $(this)).hasClass('selected')){
												 $('img.icon', $(this)).each(function(nn){
												 	$(this).addClass('hover'+(nn+1));
												 });
											}
										})
										.bind('mouseout touchend', function(e){

											//if (!$('.icon', $(this)).hasClass('selected')){
												 $('img.icon', $(this)).each(function(nn){
												 	$(this).removeClass('hover'+(nn+1));
												 });
											//}
										});

									var so = op.imgFoldersSettings,
										folderIcon = $('<div />')
										.addClass('icon');


									for (var n = 0; n < op.imgFoldersSettings.thumbNumber; n++){
										(function (){
											var iPath;

											if ($.isArray(v.images) && v.images[n]){
												iPath =
												op.axZmPath+'zoomLoad.php?'
												+'previewDir='+(data[0]+v.folderName)
												+'&previewPic='+v.images[n]
												+'&qual='+so.thumbQual
												+'&width='+(so.thumbRetina ? so.thumbWidth*2 : so.thumbWidth)
												+'&height='+(so.thumbRetina ? so.thumbHeight*2 : so.thumbHeight)
												+'&cache='+so.thumbsCache
												+'&thumbMode='+so.thumbMode
												+'&backColor='+so.thumbBackColor.replace('#', '%23')
												;
											}

											var iconImage =
											$('<img>').attr('src', op.axZmPath+'/icons/empty.gif')
											.css('width', so.thumbWidth)
											.css('height', so.thumbHeight)
											.addClass('icon')
											.addClass('icon'+(n+1))
											.css('opacity', so.thumbOpacity)
											.appendTo(folderIcon);

											if (iPath){
												// Preloading
												$('<img>').load(function(){
													iconImage
													.attr('src', iPath)
													.css({
														width: '',
														height: '',
														maxWidth: so.thumbWidth,
														maxHeight: so.thumbHeight
													})
													.removeAttr('width') // IE
													.removeAttr('height') // IE
													;

												}).attr('src', iPath);
											}
										})();
									}

									folderIcon.appendTo(newOpt);

									$('<span />').text(' ').addClass('vAlign').appendTo(newOpt);
									$('<div />').html(op.folderNameFunc(v.folderName)).addClass('descr clearfix').appendTo(newOpt);
									newOpt.appendTo(sel);
								}

								// Current folder
								if (firstFolderName == v.folderName){
									if (op.folderSelect == 'select'){
										newOpt.attr('selected', 'selected');
										if ($.fn.prop){newOpt.prop('selected', true);}
										else{newOpt.attr('selected', 'selected');}

									} else if (op.folderSelect == 'folders'){
										folderIcon
										.attr('src', op.axZmPath+'icons/'+op.folderIconPrefix+'open.png')
										.addClass('selected');
										$('div.descr', newOpt).addClass('selected');

									} else if (op.folderSelect == 'imgFolders'){
										$('img.icon', folderIcon).addClass('selected');
										$('div.descr', newOpt).addClass('selected');
									}
								}
							});

							if (firstFolderToLoad){
								loadImageThumbs('zoomDir', firstFolderToLoad);
							}

							if (op.folderSelect == 'select'){
								sel.bind('change', function(){
									if (op.setHash){
										hashChangeBlock = true;
										window.location.hash = $(this).val().split('/').reverse()[0];
									}

									loadImageThumbs('zoomDir', $(this).val())
								});
							}

							$('#'+op.selectContainer).empty().append(sel);

							if (setFromHash){
								if (op.folderSelect == 'select'){
									sel.val(setFromHash);
								}
							}
						}
					}
					else{
						// Error handling
						var errText = 'Failed to load subfolders';
						errText += '<br>Returned data is not an array, but '+(typeof data);
						$('#'+op.selectContainer).html('<div style="color: red; padding: 5px;">'+errText+'</div>');
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					// Error handling
					var errText = 'Failed to load subfolders';
					errText += '<br>Error thrown: '+jqXHR.status+' '+jqXHR.statusText;
					errText += '<br>Requested URL: '+urlLoad+'?'+dataToPass;
					$('#'+op.selectContainer).html('<div style="color: red; padding: 5px;">'+errText+'</div>');
				}
			});

		};

		// Make some checks and init
		var init = function(){
			currentLoadParam = null;
			obj = $('#'+op.thumbsContainer);

			// Get /axZm/ directory
			if (!op.axZmPath || op.axZmPath == 'auto'){
				if ($.isFunction($.fn.axZm)){
					op.axZmPath = $.fn.axZm.installPath();
				}else{
					alert('/axZm/jquery.axZm.js is not loaded');
					return;
				}
			}

			// Add slash to the /axZm path
			if (op.axZmPath.slice(-1) != '/'){
				op.axZmPath += '/';
			}

			// Test if thumbnail container exists
			if (!obj.length){
				alert('Element with ID '+op.thumbsContainer+' (thumbsContainer) was not found');
				return;
			}

			// Test if container for the folders is present
			if (op.folderSelect && !$('#'+op.selectContainer).length){
				alert('Element with ID '+op.selectContainer+' (selectContainer) was not found');
				return;
			}

			if (op.ajaxZoomOpenMode == 'zoomSwitch'){
				op.embedMode = true;
			}

			if (op.embedMode){
				op.ajaxZoomOpenMode = 'zoomSwitch';
				if (!op.embedDivID){
					alert('Option embedDivID is not defined');
					return;
				}
				if (!$('#'+op.embedDivID).length){
					alert('Element with ID '+op.embedDivID+' (embedDivID) was not found');
					return;
				}
			}

			// Remove AJAX-ZOOM if present
			if ($.axZm){
				$.fn.axZm.spinStop();
				$.fn.axZm.remove();
				$('#axZmTempBody').axZmRemove(true);
				$('#axZmTempLoading').axZmRemove(true);
				$('#axZmWrap').axZmRemove(true);
			}

			// Optionally handle hash tags
			if (op.setHash){
				$(window)
				.unbind('hashchange.azThumbGallery')
				.bind('hashchange.azThumbGallery', function(e){
					observeHash();
				});

			}

			// Check if zoomDir is in query string
			var zD = getParameterByName('zoomDir');
			if (zD){op.firstFolder = zD;}

			obj.data('aZ', op);

			// Need it only for demo
			obj.data('reloadThumbs', function(){
				displayImageThumbs(currentData, 1);
			});

			obj.data('reloadFolders', function(){
				if (sel.length){
					sel.remove();
					folderSelect();
				}
			});


			if (!op.folderSelect && op.zoomDir && op.firstFolder){
				op.zoomDir = (op.zoomDir+'/'+op.firstFolder).replace('//', '/');
			}

			// What next?
			if (op.folderSelect && op.zoomDir){
				op.zoomData = null;
				folderSelect();
			}

			// No folder selction
			else if (op.zoomDir){
				op.zoomData = null;
				loadImageThumbs('zoomDir', op.zoomDir);
			} else if (op.zoomData){
				op.zoomData = zoomDataCheck(op.zoomData);
				if (typeof op.zoomData == 'string'){
					loadImageThumbs('zoomData', op.zoomData);
				}else{
					// Error handling
				}
			}
		};

		// If $.azThumbGallery was inited before needed DOM is ready - wait
		if (!$('#'+op.thumbsContainer).length){
			$(document).ready(init);
		}else{
			init();
		}

	 };

	$.azThumbGallery.getParam = function(obj){
		var ref = $('#'+obj);
		if (ref.length > 0){
			return ref.data('aZ');
		}
		return {};
	};

})(jQuery);