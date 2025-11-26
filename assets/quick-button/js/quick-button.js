/**
 * LatePoint Quick Button - JavaScript
 * Handles any additional client-side functionality for quick buttons
 */

(function($) {
	'use strict';

	// Quick Button functionality
	var LatePointQuickButton = {
		
		init: function() {
			// Any additional initialization can go here
			// The main button injection is handled in the PHP helper
			console.log('LatePoint Quick Button addon loaded');
		}
		
	};

	// Initialize when document is ready
	$(document).ready(function() {
		LatePointQuickButton.init();
	});

})(jQuery);
