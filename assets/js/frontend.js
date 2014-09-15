/*
 * Front-end Javascript for Good Reviews for WP
 */
var grfwpCycleReviews;

jQuery(document).ready(function ($) {

	// Cycle through reviews
	grfwpCycleReviews = {

		init: function( ) {

			var start;
			for ( var i in grfwp_cycle.ids ) {
				start = $( '#gr-reviews-' +  grfwp_cycle.ids[i] ).children().first();
				start.show();
				this.cycle( start, grfwp_cycle.delay );
			}
		},

		cycle: function( el, delay ) {
			el.parent().delay( delay ).fadeOut( 500, function() {
				
				var next = el.next();
				if ( !next.length ) {
					next = el.parent().children().first();
				}

				el.hide();
				next.show();

				el.parent().fadeIn( 250, function() {
					grfwpCycleReviews.cycle( next, delay );
				});
			});
		}
	}

	grfwpCycleReviews.init();
});
