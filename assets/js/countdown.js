/**
 * GeoDirectory Event Countdown
 *
 * Renders a live countdown to each event's start time. The target is an ISO 8601
 * string with the event's timezone offset, so the absolute instant is correct
 * regardless of the visitor's timezone.
 */
jQuery( function ( $ ) {

	function pad( value ) {
		return value < 10 ? '0' + value : '' + value;
	}

	$( '.gd-event-countdown[data-countdown-to]' ).each( function () {
		var $el = $( this );
		var target = new Date( $el.attr( 'data-countdown-to' ) ).getTime();

		if ( isNaN( target ) ) {
			return;
		}

		var finished = $el.attr( 'data-finished' ) || '';
		var action = $el.attr( 'data-finished-action' ) || 'text';
		var $numbers = $el.find( '[data-countdown-number]' );
		var timer;

		function tick() {
			var distance = target - Date.now();

			if ( distance <= 0 ) {
				clearInterval( timer );

				if ( action === 'hide' ) {
					$el.hide();
				} else if ( finished ) {
					$el.html( $( '<div class="gd-event-countdown__finished" />' ).text( finished ) );
				}

				$el.addClass( 'gd-event-countdown--finished' );
				return;
			}

			var values = [
				Math.floor( distance / 86400000 ),
				Math.floor( ( distance % 86400000 ) / 3600000 ),
				Math.floor( ( distance % 3600000 ) / 60000 ),
				Math.floor( ( distance % 60000 ) / 1000 )
			];

			$numbers.each( function ( i ) {
				if ( i < values.length ) {
					$( this ).text( pad( values[ i ] ) );
				}
			} );
		}

		tick();
		timer = setInterval( tick, 1000 );
	} );
} );
