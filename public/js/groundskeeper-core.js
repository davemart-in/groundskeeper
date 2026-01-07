window.GRNDSKPR = window.GRNDSKPR || {};

/* ---- */
/* CORE MODULE */
/* ---- */
window.GRNDSKPR.Core = function () {
	'use strict';

	/* ---- */
	/* DATA */
	/* ---- */
	let data = {
		
	};

	/* ---- */
	/* INITIALIZATION */
	/* ---- */
	function init() {
		if (document.readyState === 'complete') {
			// Load data from localStorage first
			dataLoad();
			initModules();
		} else {
			window.addEventListener('load', init, false);
		}
	}

	function initModules() {
		if (window.GRNDSKPR.Terminal) {
			window.GRNDSKPR.Terminal.init();
		}
		if (window.GRNDSKPR.Canvas) {
			window.GRNDSKPR.Canvas.init();
		}
	}

	// ---------------------------------------------------
	// MESSAGE
	// ---------------------------------------------------
	function messageCheck() {
		if (typeof global !== 'undefined' && typeof global.msg !== 'undefined' && global.msg) {
			messageShow(global.msg, true);
		}
	}
	function messageHide() {
		var message = _$('.message');
		_classRemove(message, 'show');
		_classRemove(message, 'error');
	}
	function messageShow(txt, autoHide) {
		var message = _$('.message');
		// Hide message if one is already showing 
		if (_classHas(message, 'show')) {
			messageHide();
		}
		// Add text to message container
		_text(message, txt);
		// Show message
		_classAdd(message, 'show');
		// Hide on click
		_on(message, 'click', function () {
			messageHide();
		});
		// Auto hide?
		if (autoHide) {
			clearTimeout(timeoutMessage);
			timeoutMessage = setTimeout(function () {
				messageHide();
			}, 7000);
		}
	}
	// ---------------------------------------------------
	// MODAL
	// ---------------------------------------------------
	function modalEvents() {
		_on(_$('.modal_close'), 'click', function (e) {
			e.preventDefault();
			modalHide();
			return false;
		});
		if (!_isMobile() && !_isResponsive()) {
			_on(_$('.modal_backdrop'), 'click', function (e) {
				e.preventDefault();
				modalHide();
				return false;
			});
		}
		_on(_$('.modal'), 'click', function (e) {
			// Check if the clicked element or its parent is a link with target="_blank"
			let target = e.target;
			let isExternalLink = false;

			// Check up to 3 levels of parent elements
			for (let i = 0; i < 3; i++) {
				if (target && target.tagName === 'A' && target.getAttribute('target') === '_blank') {
					isExternalLink = true;
					break;
				}
				target = target.parentElement;
			}

			// Only stop propagation if it's not an external link
			if (!isExternalLink) {
				e.stopPropagation();
			}
		});
	}
	function modalHide() {
		var modalBackdrop = _$('.modal_backdrop');
		// Remove show class
		_classRemove(modalBackdrop, 'show');
		// If URL hash exists, remove it
		if (window.location.hash) {
			window.location.hash = '';
		}
		// Clear modal content
		_html(_$('.modal'), '');
		// Hide backdrop
		_hide(modalBackdrop);
	}
	function modalShow(size) {
		var modalBackdrop = _$('.modal_backdrop');
		// Show backdrop
		_show(modalBackdrop);
		// Add a small delay to ensure the modal is rendered
		setTimeout(function () {
			// Determine if modifiers need to be added
			var modal = _$('.modal');
			if (!modal) {
				console.error('Modal element not found');
				return;
			}
			if (size === 'small') {
				// Small doesn't have a class, it's just the default size
				_classRemove(modal, 'modal--medium');
				_classRemove(modal, 'modal--large');
			} else if (size === 'large') {
				_classRemove(modal, 'modal--medium');
				_classAdd(modal, 'modal--large');
			} else if (size === 'medium') {
				_classRemove(modal, 'modal--large');
				_classAdd(modal, 'modal--medium');
			}
			// Add show class
			_classAdd(modalBackdrop, 'show');
			// Events
			modalEvents();
		}, 50);
	}

	// ---------------------------------------------------
	// THEME DATA MANAGEMENT
	// ---------------------------------------------------

	function dataLoad() {
		try {
			const saved = localStorage.getItem('groundskeeper_data');
			if (saved) {
				
			}
		} catch (e) {
			console.error('Error loading data from localStorage:', e);
		}
	}

	function dataSave() {
		try {
			
		} catch (e) {
			console.error('Error saving data to localStorage:', e);
		}
	}

	/* ---- */
	/* PUBLIC METHODS */
	/* ---- */
	return {
		init: init,
		messageCheck: messageCheck,
		messageHide: messageHide,
		messageShow: messageShow,
		modalHide: modalHide,
		modalShow: modalShow,
		// Data management
		dataLoad: dataLoad,
		dataSave: dataSave,
		data: data, // Expose data object for terminal access
	};
}();

/* ---- */
/* INITIALIZE */
/* ---- */
window.GRNDSKPR.Core.init();

