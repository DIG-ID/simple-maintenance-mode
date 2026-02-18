/**
 * Customizer Preview Script
 *
 * Handles preview refresh when maintenance mode toggle changes.
 * 
 * @package    Smm
 * @subpackage Smm/admin/js
 */

(function( $ ) {
	'use strict';

	// Wait for wp.customize to be ready.
	if ( typeof wp === 'undefined' || typeof wp.customize === 'undefined' ) {
		console.error( 'SMM: wp.customize is not available!' );
		return;
	}

	// Listen to smm_enabled changes and refresh preview.
	wp.customize( 'smm_enabled', function( value ) {
		value.bind( function( to ) {
			console.log( 'SMM: Maintenance mode changed to:', to );
			// Force full page reload to show/hide template.
			wp.customize.previewer.refresh();
		});
	});

	console.log( 'SMM: Customizer preview script loaded successfully' );

})( jQuery );