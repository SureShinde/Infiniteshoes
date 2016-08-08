/**
* Plugin: jQuery AJAX-ZOOM, jquery.axZm.expButton.js
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Extension Version: 1.4
* Extension Date: 2016-04-25
* URL: http://www.ajax-zoom.com
* Documentation: http://www.ajax-zoom.com/index.php?cid=docs
*/

;(function($){

	 $.axZmEb = function(opt){
	 		
	 	var def = {
	 		title: null, // title of the button, can be omited - opens instantly in this case. 
	 		descr: null, // description when opened, can be omited - does not open then. Shortcuts - iframe:scr and ajax:url
	 		gravity: 'top', // possible values: topLeft, top, topRight, bottomLeft, bottom, bottomRight, center
	 		iframeHeader: false,
	 		marginX: 5, // horizontal margin depending on gravity
	 		marginY: 5, // vertical margin depending on gravity
	 		openSpeed: 300, // duration of open animation in ms
	 		closeSpeed: 150, // duration of close animation in ms
	 		fadeInSpeed: 200, // fadein duration of the button
	 		autoOpen: false, // button opens instantly; if no tilte but descr is defined, autoOpen executes instantly
	 		removeOnClose: false, // removes button when extended state is closed
	 		zoomSpinPanRemove: false, // removes button / layer when there is some action inside AJAX-ZOOM
	 		classPref: 'axZmEb_', // prefix for the css classes, see jquery.axZm.buttonDescr.css
	 		arrow: true, // show arrow inside button indicating that it is expandable
	 		single: true, // closeses other instances instantly
	 		thumbSlider: true, // uses axZmThumbSlider content mode for overflow text (requires axZmThumbSlider JS and CSS files)
	 		tapHide: true, // temporary disables all other layers inside AJAX-ZOOM player
	 		dblClickClose: true, // closes expanded / opened state on double click
	 		par: '#axZm_zoomLayer', // layer to place the button
	 		parO: '#axZm_zoomLayer', // layer to attach when opened; if body it will be fullscreen
	 		// options passed to axZmThumbSlider when thumbSlider option is enabled
	 		// here thumbSlider is used to scroll long content (descr)
	 		thumbSliderOpt: { 
	 			contentMode: true,
				centerNoScroll: false,
				outerWrapPosition: 'absolute',
				contentStyle: {background: 'none', padding: 0, paddingRight: 25},
				scrollbarTrackStyle: {background: 'none'},
				wrapStyle: {borderWidth: 0},
				btn: false,
				orientation: 'vertical',
				scrollbar: true // apply thumb slider
			},
			onCloseEnd: null, // callback on close
			onOpenEnd: null, // callback when button openes / expands
			onAjaxLoad: null // if descr is ajax call - the callback on success
		};
		
	    var isObject = function(x) {
	        return (typeof x === 'object') && !(x instanceof Array) && (x !== null);
	    };
	    
		var o = $.extend(true, {}, def, opt),
			classPref = o.classPref,
	 		overflow = {},
	 		overflowPar = {},
	 		top = '', 
	 		left = '', 
	 		bottom = '',
	 		right = '', 
	 		marginTop = '', 
	 		marginLeft = '',
	 		noTitle = false,
	 		ajx = false,
	 		clM = {
	 			Wrap: classPref+'Wrap',
	 			InnerClick: classPref+'InnerClick',
	 			Inner: classPref+'Inner',
	 			Text: classPref+'Text',
	 			TextOpened: classPref+'TextOpened',
	 			Drop: classPref+'Drop',
	 			Drop_down: classPref+'Drop_down',
	 			Drop_up: classPref+'Drop_up',
	 			Descr: classPref+'Descr',
	 			DescrScroll: classPref+'DescrScroll',
	 			DescrIframe: classPref+(o.iframeHeader ? 'DescrIframe' : 'DescrIframeNoHeader'),
	 			DescrAjax: classPref+'DescrAjax',
	 			DescrNoTitle: classPref+'DescrNoTitle',
	 			WrapClose: classPref+'WrapClose',
	 			WrapClose_over: classPref+'WrapClose_over',
	 			WrapCloseIframe: classPref+'WrapCloseIframe',
	 			WrapCloseIframe_over: classPref+'WrapCloseIframe_over'
			};
		
		if (o.title){
			o.title = o.title.replace(/&#34;/g,'\"');
		}
		if (o.descr){
			o.descr = o.descr.replace(/&#34;/g,'\"');
		}
		
		var supportFixed = function(){
		    var elem = document.createElement('div');
		    elem.style.cssText = 'position:fixed';
		    if (elem.style.position.match('fixed')) {return true;}
		    else {return false;}
		};

		if (o.single){
			var toRemove = $('.'+clM.Wrap);
			if (toRemove.length > 0){
				toRemove.each(function(){
					var _this = $(this),
					_thisDataO = _this.data('overflow');
					if (!$.isEmptyObject(_thisDataO)){
						$('body').css({
							overflowX: _thisDataO.x,
							overflowY: _thisDataO.y
						});
					}
					_this.remove();
				});
			}
		}
		
		if (o.zoomSpinPanRemove && $.axZm){
			if (!isObject($.axZm.userFunc.onZoomSpinPanOnce)){
				var tempFunc = $.axZm.userFunc.onZoomSpinPanOnce;
				$.axZm.userFunc.onZoomSpinPanOnce = {};
				if ($.isFunction(tempFunc)){
					$.axZm.userFunc.onZoomSpinPanOnce[new Date().getTime()] = tempFunc;
				}
			}
			
			$.axZm.userFunc.onZoomSpinPanOnce['axZmEb1'] = function(){
  				$('.'+clM.Wrap, o.par).remove();
  				$('.'+clM.Wrap, o.parO).remove();
  			};
  			
  			$.axZm.userFunc.onZoomSpinPanOnce['axZmEb2'] = function(){
  				if ($.isFunction($.axZmThumbSlider) && typeof o.zoomSpinPanRemove == 'string'){
					$('#'+o.zoomSpinPanRemove).axZmThumbSlider('unselect');
				} else if (typeof o.zoomSpinPanRemove == 'string'){
					$('li', $('#'+o.zoomSpinPanRemove)).removeClass('selected');
				}
			}
		}
		
		if (!o.title && o.descr){
			o.title = " ";
			o.autoOpen = true;
			o.removeOnClose = true;
			noTitle = true;
		}else if (!o.title){
			return false;
		}
		
		if (o.autoOpen){
			o.fadeInSpeed = 0;
		}
	
		var grav = o.gravity.toLowerCase();
		
        switch (grav) {
            case 'topleft':
                left = o.marginX;
                top = o.marginY;
                break;
            case 'top':
                left = '50%';
                top = o.marginY;
                break;
            case 'topright':
                right = o.marginX;
                top = o.marginY;
                break;
            case 'bottomright':
                right = o.marginX;
                bottom = o.marginY;
                break;
            case 'bottom':
                left = '50%';
                bottom = o.marginY;
                break;
            case 'bottomleft':
                left = o.marginX;
                bottom = o.marginY;
                break;
            case 'center':
                top = '50%';
                left = '50%';
                break;
        }
        
        if (/(bottom)/gm.test(o.gravity)){
        	clM.Drop_down = clM.Drop_up;
		}
		
		if (o.parO == 'body' || o.parO == 'window'){
			o.parO = 'body';
			
			overflow = {
				x: $('body').css('overflowX'),
				y: $('body').css('overflowY')
			};
			
			var sF = supportFixed();

			if (sF){
				overflowPar = {position: 'fixed'};
			}else{
				overflowPar = {top: true};
			}
			
			if (o.par == 'body' || o.par == 'window'){
				o.par = 'body';
				o.removeOnClose = true;
				o.autoOpen = true;
			}
		}
		
		var _Text = $('<div />').addClass(clM.Text).html(o.title),
			_Inner = $('<div />').addClass(clM.Inner)
			.append(_Text);
		
		
		if (grav != 'top' && grav != 'bottom' && grav != 'center'){
			_Inner.addClass(classPref+'InnerCorner');
		}
		
		// Add button
		var _tButton = $('<div />')
		.addClass(clM.Wrap)
		.data('overflow', overflow)
		.append(_Inner)
		.css({
			'opacity': 0,
			'top': top,
			'right': right,
			'bottom': bottom,
			'left': left
		})
		.appendTo(o.par)
		.animate({
			opacity: 1
		},{
			duration: o.fadeInSpeed,
			complete: function(){
				$(this).css('opacity', '')
			}
		});
		
		var closeDescr = function(){
			
			if ($.axZm && o.tapHide){$.fn.axZm.tapShow();}

			if (o.par != o.parO){
				_tButton.appendTo(o.par).css('position', 'absolute');
			}
			_Text.removeClass(clM.TextOpened);
						
			$('[class^='+clM.WrapClose+']', _tButton).remove();
			$('.'+clM.Descr, _tButton).fadeOut(o.closeSpeed/2, function(){$(this).remove();});
			if (!noTitle && o.arrow){
				$('.'+clM.Drop, _tButton).css('display', '').removeClass(clM.Drop_down);
			}
			
			var d = _tButton.data();
			
			var anmCss = {
				width: d.w,
				height: d.h
			};
			
			if (top){anmCss.top = top;}
			if (right){anmCss.right = right;}
			if (bottom){anmCss.bottom = bottom;}
			if (left){anmCss.left = left;}
			
			_tButton.animate(anmCss,{
				duration: o.closeSpeed
			});
			
			$('div:eq(0)', _tButton)
			//.css('overflowY', 'hidden')
			.animate({
				width: d.w1,
				height: d.h1,
				left: d.l,
				paddingTop: d.pT,
				paddingRight: d.pR, 
				paddingBottom: d.pB, 
				paddingLeft: d.pL,
				borderTopLeftRadius: d.bRa,
				borderTopRightRadius: d.bRa,
				borderBottomLeftRadius: d.bRa,
				borderBottomRightRadius: d.bRa
			},{
				duration: o.closeSpeed,
				complete: function(){
					$(this).removeAttr('style');
					_tButton.css({
						width: '',
						height: '',
						'top': top,
						'right': right,
						'bottom': bottom,
						'left': left,
						'zIndex': d.zI
					});
					
					if (o.removeOnClose){
						_tButton.axZmRemove();
					}
							
					if (!$.isEmptyObject(overflow)){
						$('body').css({
							overflowY: overflow.x,
							overflowY: overflow.y
						});
					}
					
					if (!noTitle && o.arrow){ // IE < 9
						$('.'+clM.Drop, _tButton).css('display', '').removeClass(clM.Drop_down);
					}
				}
			});
		};
		
		if (o.descr){

			if (!noTitle && o.arrow){
				$('<div />').addClass(clM.Drop).prependTo(_Text);
			}
			
			$('.'+clM.Inner, _tButton)
			.addClass(clM.InnerClick)
			.bind('mousedown', function(e){
				e.stopPropagation();
			})
			.bind('mouseover', function(){
				if (!noTitle && o.arrow){
					$('.'+clM.Drop, _tButton).addClass(clM.Drop_down);
				}
			})
			.bind('mouseout', function(){
				if (!noTitle && o.arrow){
					$('.'+clM.Drop, _tButton).removeClass(clM.Drop_down);
				}
			})
			.bind('click', function(e){
				e.stopPropagation();
				// Expand
				if (_tButton.css('zIndex') < 10){
				
					if ($.axZm && o.tapHide){$.fn.axZm.tapHide([clM.Wrap]);}
					var _bC =  $('div:eq(0)',_tButton);
					var isIframe = false; 
					
					if (!_tButton.data('w')){
						_tButton.data({
							w: _tButton.width(), 
							h: _tButton.height(), 
							l: _bC.css('left'),
							w1: _bC.width(), 
							h1: _bC.height(), 
							zI: _tButton.css('zIndex'), 
							pT: _bC.css('paddingTop'), 
							pR: _bC.css('paddingRight'), 
							pB: _bC.css('paddingBottom'), 
							pL: _bC.css('paddingLeft'),
							bRa: _bC.css('borderTopLeftRadius')
						});
					}
					
					_Text.addClass(clM.TextOpened);
					$('.'+clM.Drop, _tButton).css('display', 'none');
					
					var anmCss = {
						height: '100%',
						width: '100%'
					};
					
					if (top){anmCss.top = 0;}
					if (right){anmCss.right = 0;}
					if (bottom){anmCss.bottom = 0;}
					if (left){anmCss.left = 0;}

					// fixed not supported
					if (overflowPar.top >= 0){
						overflowPar.top = $(window).scrollTop()
						if (anmCss.top >= 0){
							anmCss.top = overflowPar.top;
						}else{
							anmCss.bottom = -overflowPar.top;
						}
					}
					
					// fixed
					if (o.parO == 'body' && o.par != 'body'){
						
						var ofs = _tButton.offset(),
							scrT = $(window).scrollTop(),
							scrL = $(window).scrollLeft();
						
						if (!overflowPar.position){
							scrT = 0; scrL = 0;
						}
							
						if (top){_tButton.css('top', ofs.top - scrT)}
						if (right){_tButton.css('right', $(window).width() - ofs.left + scrL - _tButton.outerWidth())}
						if (bottom){_tButton.css('bottom', $(window).height() - ofs.top + scrT - _tButton.outerHeight())}
						if (left){_tButton.css('left', ofs.left - scrL)}
					}
					
					if (o.parO == 'body'){
						_tButton.appendTo('body');
						$('body').css('overflowX', 'hidden');
						$('body').css('overflowY', 'hidden');
					}

					if (overflowPar.position){
						_tButton.css('position', overflowPar.position)
					}
					
					_tButton
					.css({zIndex: 2147483647})
					.animate(anmCss,{
						duration: o.openSpeed
					});
					
					//o.descr = tDescr.replace(/\n/g, "<br />")
					o.descr.replace(/\\"/g, '"');
					
					// Iframe shortcut
					if (o.descr.indexOf('iframe:') == 0){
						o.descr = o.descr.replace('iframe:', '');
						o.descr = "<iframe src='"+o.descr+"' allowfullscreen></iframe>";
					}


					// AJAX
					if (o.descr.indexOf('ajax:') == 0){
						o.descr = o.descr.replace('ajax:', '');
						_descrContent = $('<div class="'+clM.Descr+'"></div>').addClass(clM.DescrAjax);
						ajx = true;
						$.ajax({
			                url: o.descr,
			                type: 'GET',
			                cache: false,
			                error: function(){
								_descrContent.html('Error<br><br>AJAX request:<br><br>'+o.descr+'<br><br>could not be loaded')
								.removeClass(clM.DescrAjax);
			                },
			                success: function (data){
			                    _descrContent.html(data).removeClass(clM.DescrAjax);
			                    if ($.isFunction(o.onAjaxLoad)){
			                    	o.onAjaxLoad(data)
								}
			                }
			            });
					}
					
					if (!ajx){
						var _descrContent = $('<div class="'+clM.Descr+'">'+o.descr+'</div>');
						
						// check iframe
						var descrLen = _descrContent.children().length;
							
						if (descrLen == 1){
							var _testIframe = $('iframe', _descrContent);
							if (_testIframe.length == 1){
								isIframe = true;
								_descrContent.addClass(clM.DescrIframe);
								
								_testIframe
								.attr('frameborder', 0)
								.css({width: '100%', height: '100%'});
							}
						}
					}
					
					
					var _WrapClose = 
					$('<div />').addClass(isIframe && !o.iframeHeader ? clM.WrapCloseIframe : clM.WrapClose)
					.bind('click', closeDescr)
					.bind('mouseover', function(){$(this).addClass(isIframe && !o.iframeHeader ? clM.WrapCloseIframe_over : clM.WrapClose_over)})
					.bind('mouseout', function(){$(this).removeClass(isIframe && !o.iframeHeader ? clM.WrapCloseIframe_over : clM.WrapClose_over)})
					
					if (noTitle){
						_descrContent.addClass(clM.DescrNoTitle);
					}

					_bC
					.append(_descrContent)
					.append(_WrapClose)
					.animate({
						left: '0%',
						top: '0%',
						height: '100%',
						width: '100%',
						padding: 0,
						'border-radius': 0
					},{
						duration: o.openSpeed,
						complete: function(){
							_bC.css({
								overflowY: 'auto',
								cursor: 'auto'
							});
							
							if (o.dblClickClose){
								_bC.unbind('dblclick', closeDescr).bind('dblclick', closeDescr);
							}
							
							// Scroller ?
							if (!isIframe && o.thumbSlider && $.isFunction($.fn.axZmThumbSlider)){
								_descrContent.axZmThumbSlider(o.thumbSliderOpt);
							}else if (!isIframe){
								_descrContent.addClass(clM.DescrScroll)
								.mousewheel(function(event){
					                event.stopPropagation();
					            });
							}

							if ($.isFunction(o.onOpenEnd)){
								o.onOpenEnd(_tButton);
							}
						}

					});
				}
			});
			
			if (o.autoOpen){
				$('.'+clM.Inner, _tButton).trigger('click');
			}
		}
		
		return _tButton;
	 };
	 
	 $.fn.axZmEb = function(opt, arg) {
	 	return this.each(function() {
	 		opt.par = $(this);
	 		if (!opt.parO){opt.parO = $(this);}
	 		$.axZmEb(opt);
		});
	 };
})(jQuery);