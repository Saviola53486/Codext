;( function( $, window, document, undefined )
{
	'use strict';

	$(document).ready(function() {

		var zn_sideHeader = function() {

			var ID = function () {
					return '_' + Math.random().toString(36).substr(2, 9);
				},
				sideHeader = $('#zn-side-header'),
				mainMenu = $('#side-main-nav'),
				mainMenuContainer = $('#side-main-menu'),
				sidePanelMinimize = parseInt( sideHeader.attr('data-minimize') );

			// Minimize Panel
			$(window).on('debouncedresize', function(){

				if(window.matchMedia('(min-width: '+ (sidePanelMinimize+1) +'px)').matches){
					sideHeader.removeClass('is-opened is-under-minimize');
				}
				else {
					if ( !sideHeader.hasClass('is-under-minimize') )
						sideHeader.addClass('is-under-minimize');
				}
			}).trigger('debouncedresize');

			// Trigger Burger Menu
			$('#znSdHead-burger').on('click', function(e){

				if ( !sideHeader.hasClass('is-opened') && window.matchMedia('(max-width: '+ sidePanelMinimize +'px)').matches ) {
					sideHeader.addClass('is-opened');
				}
				else {
					sideHeader.removeClass('is-opened');
				}
			});

			mainMenuContainer.addClass('is-loaded');

			if( !mainMenuContainer.hasClass('side-main-menu--depth1') ){

				// Prepare Sub-Menus
				mainMenu.find('ul').each(function(index, el) {
					var $el = $(el),
						$uid = ID();
					// name parent
					$el.prev('a').attr('data-submenu', $uid);
					// name submenu
					$el.attr('data-menu', $uid);
					// Detach & move after main menu
					$el.detach().insertAfter(mainMenu);
				});

				// Start MLM
				var benable = mainMenuContainer.is('[data-show-breadcrumbs]') ? true : false;
				var mlmenu = new MLMenu(mainMenuContainer[0], {
					breadcrumbsCtrl : benable, // show breadcrumbs
					initialBreadcrumb : mainMenuContainer.is('[data-breadcrumb-text]') ? mainMenuContainer.attr('data-breadcrumb-text') : '',
					backCtrl : true, // show back button
					// itemsDelayInterval : 60, // delay between each menu item sliding animation
					// onItemClick: loadDummyData // callback: item that doesn´t have a submenu gets clicked - onItemClick([event], [inner HTML of the clicked item])
				});
			}
		};

		zn_sideHeader();

	});

	/**
	 * codrops/MultiLevelMenu
	 * A simple menu with multiple levels and an optional breadcrumb navigation and back button.
	 * https://github.com/codrops/MultiLevelMenu
	 */

	var animEndEventNames = { 'WebkitAnimation' : 'webkitAnimationEnd', 'OAnimation' : 'oAnimationEnd', 'msAnimation' : 'MSAnimationEnd', 'animation' : 'animationend' },
		animEndEventName = animEndEventNames[ Modernizr.prefixed( 'animation' ) ],
		onEndAnimation = function( el, callback ) {
			var onEndCallbackFn = function( ev ) {
				if( ev.target != this ) return;
				this.removeEventListener( animEndEventName, onEndCallbackFn );

				if( callback && typeof callback === 'function' ) { callback.call(); }
			};
			el.addEventListener( animEndEventName, onEndCallbackFn );
		};

	function extend( a, b ) {
		for( var key in b ) {
			if( b.hasOwnProperty( key ) ) {
				a[key] = b[key];
			}
		}
		return a;
	}

	function MLMenu(el, options) {
		this.el = el;
		this.options = extend( {}, this.options );
		extend( this.options, options );

		// the menus (<ul>´s)
		this.menus = [].slice.call(this.el.querySelectorAll('.znSdHead-menuList'));
		// index of current menu
		this.current = 0;

		this._init();
	}

	MLMenu.prototype.options = {

		breadcrumbsCtrl : true,			// show breadcrumbs
		initialBreadcrumb : 'all',		// initial breadcrumb text
		backCtrl : true,				// show back button
		itemsDelayInterval : 60,		// delay between each menu item sliding animation
		heightTolerance : 70,			// extra height
		direction : 'r2l',				// direction
		// callback: item that doesn´t have a submenu gets clicked
		// onItemClick([event], [inner HTML of the clicked item])
		onItemClick : function(ev, itemName) { return false; }
	};

	MLMenu.prototype._init = function() {
		// iterate the existing menus and create an array of menus, more specifically an array of objects where each one holds the info of each menu element and its menu items
		this.menusArr = [];
		var self = this;
		this.menus.forEach(function(menuEl, pos) {
			var menu = {menuEl : menuEl, menuItems : [].slice.call(menuEl.querySelectorAll('.znSdHead-menuList-item'))};
			// console.log(menu);
			self.menusArr.push(menu);

			// set current menu class
			if( pos === self.current ) {
				classie.add(menuEl, 'znSdHead-menuList--current');
				classie.remove(menuEl, 'is-first');
			}
		});

		var currentMenuHeight = self.el.querySelector('.znSdHead-menuList--current');
		self.el.style.minHeight = (currentMenuHeight.clientHeight + self.options.heightTolerance) + 'px';

		// create back button
		if( this.options.backCtrl ) {
			var menuBack = document.getElementById('znSdHead-menuBack');
			if(!menuBack)
			{
				this.backCtrl = document.createElement('button');
				this.backCtrl.className = 'znSdHead-menuBack znSdHead-menuBack--hidden';
				this.backCtrl.setAttribute('aria-label', 'Go back');
				this.backCtrl.innerHTML = '<span class="icon-znshfont-arrow-left"></span>';
				this.el.insertBefore(this.backCtrl, this.el.firstChild);
			}
			else {
				this.backCtrl = menuBack;
			}
		}

		// create breadcrumbs
		if( self.options.breadcrumbsCtrl ) {
			var menuBrc = document.getElementById('znSdHead-menuBrc');
			if(!menuBrc)
			{
				this.breadcrumbsCtrl = document.createElement('nav');
				this.breadcrumbsCtrl.className = 'znSdHead-menuBrc';
				this.el.insertBefore(this.breadcrumbsCtrl, this.el.firstChild);
			}
			else {
				this.breadcrumbsCtrl = menuBrc;
				this._emptyBreadcrumb();
			}
			// add initial breadcrumb
			this._addBreadcrumb(0);
		}

		// event binding
		this._initEvents();
	};

	MLMenu.prototype._initEvents = function() {
		var self = this;


		for(var i = 0, len = this.menusArr.length; i < len; ++i) {
			this.menusArr[i].menuItems.forEach(function(item, pos) {
				item.querySelector('a').addEventListener('click', function(ev) {
					var submenu = ev.target.getAttribute('data-submenu'),
						itemName = ev.target.innerHTML,
						subMenuEl = self.el.querySelector('ul[data-menu="' + submenu + '"]');
					// check if there's a sub menu for this item
					if( submenu && subMenuEl ) {
						ev.preventDefault();
						// open it
						self._openSubMenu(subMenuEl, pos, itemName);
					}
					else {
						// add class current
						var currentlink = self.el.querySelector('.znSdHead-menuList-link--current');
						if( currentlink ) {
							classie.remove(self.el.querySelector('.znSdHead-menuList-link--current'), 'znSdHead-menuList-link--current');
						}
						classie.add(ev.target, 'znSdHead-menuList-link--current');

						// callback
						self.options.onItemClick(ev, itemName);
					}
				});
			});
		}

		// back navigation
		if( this.options.backCtrl ) {
			this.backCtrl.addEventListener('click', function() {
				self._back();
			});
		}
	};

	MLMenu.prototype._openSubMenu = function(subMenuEl, clickPosition, subMenuName) {
		if( this.isAnimating ) {
			return false;
		}
		this.isAnimating = true;

		// save "parent" menu index for back navigation
		this.menusArr[this.menus.indexOf(subMenuEl)].backIdx = this.current;
		// save "parent" menu´s name
		this.menusArr[this.menus.indexOf(subMenuEl)].name = subMenuName;
		// current menu slides out
		this._menuOut(clickPosition);
		// next menu (submenu) slides in
		this._menuIn(subMenuEl, clickPosition);
	};

	MLMenu.prototype._back = function() {
		if( this.isAnimating ) {
			return false;
		}
		this.isAnimating = true;

		// current menu slides out
		this._menuOut();
		// next menu (previous menu) slides in
		var backMenu = this.menusArr[this.menusArr[this.current].backIdx].menuEl;
		this._menuIn(backMenu);

		// remove last breadcrumb
		if( this.options.breadcrumbsCtrl ) {
			this.breadcrumbsCtrl.removeChild(this.breadcrumbsCtrl.lastElementChild);
		}
	};

	MLMenu.prototype._menuOut = function(clickPosition) {
		// the current menu
		var self = this,
			currentMenu = this.menusArr[this.current].menuEl,
			isBackNavigation = typeof clickPosition == 'undefined' ? true : false;

		// slide out current menu items - first, set the delays for the items
		this.menusArr[this.current].menuItems.forEach(function(item, pos) {
			item.style.WebkitAnimationDelay = item.style.animationDelay = isBackNavigation ? parseInt(pos * self.options.itemsDelayInterval) + 'ms' : parseInt(Math.abs(clickPosition - pos) * self.options.itemsDelayInterval) + 'ms';
		});
		// animation class
		if( this.options.direction === 'r2l' ) {
			classie.add(currentMenu, !isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
		}
		else {
			classie.add(currentMenu, isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
		}
	};

	MLMenu.prototype._menuIn = function(nextMenuEl, clickPosition) {
		var self = this,
			// the current menu
			currentMenu = this.menusArr[this.current].menuEl,
			isBackNavigation = typeof clickPosition == 'undefined' ? true : false,
			// index of the nextMenuEl
			nextMenuIdx = this.menus.indexOf(nextMenuEl),

			nextMenuItems = this.menusArr[nextMenuIdx].menuItems,
			nextMenuItemsTotal = nextMenuItems.length;

		// Set Min Height
		self.el.style.minHeight = (nextMenuEl.clientHeight + self.options.heightTolerance) + 'px';

		// slide in next menu items - first, set the delays for the items
		nextMenuItems.forEach(function(item, pos) {
			item.style.WebkitAnimationDelay = item.style.animationDelay = isBackNavigation ? parseInt(pos * self.options.itemsDelayInterval) + 'ms' : parseInt(Math.abs(clickPosition - pos) * self.options.itemsDelayInterval) + 'ms';

			// we need to reset the classes once the last item animates in
			// the "last item" is the farthest from the clicked item
			// let's calculate the index of the farthest item
			var farthestIdx = clickPosition <= nextMenuItemsTotal/2 || isBackNavigation ? nextMenuItemsTotal - 1 : 0;

			if( pos === farthestIdx ) {
				onEndAnimation(item, function() {
					// reset classes
					if( self.options.direction === 'r2l' ) {
						classie.remove(currentMenu, !isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
						classie.remove(nextMenuEl, !isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
					}
					else {
						classie.remove(currentMenu, isBackNavigation ? 'animate-outToLeft' : 'animate-outToRight');
						classie.remove(nextMenuEl, isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
					}
					classie.remove(currentMenu, 'znSdHead-menuList--current');
					classie.add(nextMenuEl, 'znSdHead-menuList--current');

					//reset current
					self.current = nextMenuIdx;

					// control back button and breadcrumbs navigation elements
					if( !isBackNavigation ) {
						// show back button
						if( self.options.backCtrl ) {
							classie.remove(self.backCtrl, 'znSdHead-menuBack--hidden');
						}

						// add breadcrumb
						self._addBreadcrumb(nextMenuIdx);
					}
					else if( self.current === 0 && self.options.backCtrl ) {
						// hide back button
						classie.add(self.backCtrl, 'znSdHead-menuBack--hidden');
					}

					// we can navigate again..
					self.isAnimating = false;
				});
			}
		});

		// animation class
		if( this.options.direction === 'r2l' ) {
			classie.add(nextMenuEl, !isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
		}
		else {
			classie.add(nextMenuEl, isBackNavigation ? 'animate-inFromRight' : 'animate-inFromLeft');
		}
	};


	MLMenu.prototype._emptyBreadcrumb = function() {
		if( !this.options.breadcrumbsCtrl ) {
			return false;
		}
		if( this.breadcrumbsCtrl.hasChildNodes()){
			this.breadcrumbsCtrl.childNodes[0].remove();
		}
	};

	MLMenu.prototype._addBreadcrumb = function(idx) {
		if( !this.options.breadcrumbsCtrl ) {
			return false;
		}

		var bc = document.createElement('a');
		bc.innerHTML = idx ? this.menusArr[idx].name : this.options.initialBreadcrumb;
		this.breadcrumbsCtrl.appendChild(bc);

		var self = this;

		bc.addEventListener('click', function(ev) {
			ev.preventDefault();

			// do nothing if this breadcrumb is the last one in the list of breadcrumbs
			if( !bc.nextSibling || self.isAnimating ) {
				return false;
			}
			self.isAnimating = true;

			// current menu slides out
			self._menuOut();
			// next menu slides in
			var nextMenu = self.menusArr[idx].menuEl;
			self._menuIn(nextMenu);

			// remove breadcrumbs that are ahead
			var siblingNode;
			while (siblingNode = bc.nextSibling) {
				self.breadcrumbsCtrl.removeChild(siblingNode);
			}
		});

	};

	window.MLMenu = MLMenu;

	/*!
	 * classie v1.0.1
	 * class helper functions
	 * from bonzo https://github.com/ded/bonzo
	 * MIT license
	 *
	 * classie.has( elem, 'my-class' ) -> true/false
	 * classie.add( elem, 'my-new-class' )
	 * classie.remove( elem, 'my-unwanted-class' )
	 * classie.toggle( elem, 'my-class' )
	 */

	// class helper functions from bonzo https://github.com/ded/bonzo

	function classReg( className ) {
	  return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
	}

	// classList support for class management
	// altho to be fair, the api sucks because it won't accept multiple classes at once
	var hasClass, addClass, removeClass;

	if ( 'classList' in document.documentElement ) {
	  hasClass = function( elem, c ) {
	    return elem.classList.contains( c );
	  };
	  addClass = function( elem, c ) {
	    elem.classList.add( c );
	  };
	  removeClass = function( elem, c ) {
	    elem.classList.remove( c );
	  };
	}
	else {
	  hasClass = function( elem, c ) {
	    return classReg( c ).test( elem.className );
	  };
	  addClass = function( elem, c ) {
	    if ( !hasClass( elem, c ) ) {
	      elem.className = elem.className + ' ' + c;
	    }
	  };
	  removeClass = function( elem, c ) {
	    elem.className = elem.className.replace( classReg( c ), ' ' );
	  };
	}

	function toggleClass( elem, c ) {
	  var fn = hasClass( elem, c ) ? removeClass : addClass;
	  fn( elem, c );
	}

	var classie = {
	  // full names
	  hasClass: hasClass,
	  addClass: addClass,
	  removeClass: removeClass,
	  toggleClass: toggleClass,
	  // short names
	  has: hasClass,
	  add: addClass,
	  remove: removeClass,
	  toggle: toggleClass
	};

	// transport
	if ( typeof define === 'function' && define.amd ) {
	  // AMD
	  define( classie );
	} else if ( typeof exports === 'object' ) {
	  // CommonJS
	  module.exports = classie;
	} else {
	  // browser global
	  window.classie = classie;
	}

})( jQuery, window, document );