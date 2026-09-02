<?php
/**
 * Opening hours: parsed once from the settings, rendered as a status line and
 * a table.
 *
 * The settings screen stores hours in Schema.org notation, one rule per line
 * ("Mo-Fr 10:00-18:00"), because that is what the LocalBusiness node needs. A
 * visitor needs the same facts in another shape: is it open right now, until
 * when, and a table they can read. Both come from the same lines, so the site
 * cannot tell Google one thing and the visitor another.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schema.org day tokens in Monday-first order, mapped to PHP's N (1 = Monday).
 *
 * @return array<string, int>
 */
function basalt_core_day_tokens(): array {
	return array(
		'Mo' => 1,
		'Tu' => 2,
		'We' => 3,
		'Th' => 4,
		'Fr' => 5,
		'Sa' => 6,
		'Su' => 7,
	);
}

/**
 * Parse the opening hours setting into intervals per weekday.
 *
 * Overlapping intervals on one day are merged, so "Mo-Fr 10:00-18:00" followed
 * by "Th 10:00-20:00" gives Thursday a single 10:00 to 20:00 span, which is
 * what both rules together mean.
 *
 * @param string|null $raw The setting, or null to read it.
 * @return array<int, array<int, array{0: int, 1: int}>> Weekday (1 to 7) to list of [open, close] in minutes.
 */
function basalt_core_opening_hours( ?string $raw = null ): array {
	if ( null === $raw ) {
		$raw = (string) basalt_core_get( 'opening_hours' );
	}

	$tokens = basalt_core_day_tokens();
	$hours  = array_fill_keys( range( 1, 7 ), array() );

	foreach ( basalt_core_parse_lines( $raw ) as $line ) {
		if ( ! preg_match( '/^([A-Za-z,\-\s]+?)\s+(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})$/', $line, $m ) ) {
			continue;
		}

		$open  = ( (int) $m[2] ) * 60 + (int) $m[3];
		$close = ( (int) $m[4] ) * 60 + (int) $m[5];

		if ( $close <= $open ) {
			continue;
		}

		foreach ( basalt_core_expand_days( $m[1], $tokens ) as $day ) {
			$hours[ $day ][] = array( $open, $close );
		}
	}

	foreach ( $hours as $day => $intervals ) {
		$hours[ $day ] = basalt_core_merge_intervals( $intervals );
	}

	/**
	 * Filter the parsed opening hours.
	 *
	 * @param array<int, array<int, array{0: int, 1: int}>> $hours Weekday to intervals in minutes.
	 */
	return (array) apply_filters( 'basalt_core_opening_hours', $hours );
}

/**
 * Expand "Mo-Fr", "Mo,We" or "Sa" into weekday numbers.
 *
 * @param string             $spec   The day part of a rule.
 * @param array<string, int> $tokens Token to weekday map.
 * @return int[]
 */
function basalt_core_expand_days( string $spec, array $tokens ): array {
	$days = array();

	foreach ( explode( ',', $spec ) as $part ) {
		$part = trim( $part );

		if ( str_contains( $part, '-' ) ) {
			list( $from, $to ) = array_map( 'trim', explode( '-', $part, 2 ) );

			if ( ! isset( $tokens[ $from ], $tokens[ $to ] ) ) {
				continue;
			}

			$a = $tokens[ $from ];
			$b = $tokens[ $to ];

			// "Sa-Mo" wraps around the week.
			for ( $d = $a; ; $d = ( $d % 7 ) + 1 ) {
				$days[] = $d;

				if ( $d === $b ) {
					break;
				}
			}
		} elseif ( isset( $tokens[ $part ] ) ) {
			$days[] = $tokens[ $part ];
		}
	}

	return array_values( array_unique( $days ) );
}

/**
 * Merge overlapping or touching intervals.
 *
 * @param array<int, array{0: int, 1: int}> $intervals Unsorted intervals.
 * @return array<int, array{0: int, 1: int}>
 */
function basalt_core_merge_intervals( array $intervals ): array {
	usort( $intervals, static fn( array $a, array $b ): int => $a[0] <=> $b[0] );

	$merged = array();

	foreach ( $intervals as $interval ) {
		$last = count( $merged ) - 1;

		if ( $last >= 0 && $interval[0] <= $merged[ $last ][1] ) {
			$merged[ $last ][1] = max( $merged[ $last ][1], $interval[1] );
		} else {
			$merged[] = $interval;
		}
	}

	return $merged;
}

/**
 * Format minutes since midnight in the site's time format.
 *
 * @param int $minutes Minutes since midnight.
 * @return string
 */
function basalt_core_format_minutes( int $minutes ): string {
	$time = ( new DateTimeImmutable( 'today', wp_timezone() ) )->setTime( intdiv( $minutes, 60 ), $minutes % 60 );

	return wp_date( (string) get_option( 'time_format', 'H:i' ), $time->getTimestamp() );
}

/**
 * The status right now: open until, opens at, or closed until another day.
 *
 * @param array<int, array<int, array{0: int, 1: int}>>|null $hours Parsed hours, or null to read them.
 * @param DateTimeImmutable|null                            $now   The moment to evaluate, defaults to now.
 * @return array{state: string, text: string} state is "open", "opens-later" or "closed".
 */
function basalt_core_opening_status( ?array $hours = null, ?DateTimeImmutable $now = null ): array {
	$hours = $hours ?? basalt_core_opening_hours();
	$now   = $now ?? new DateTimeImmutable( 'now', wp_timezone() );

	$today   = (int) $now->format( 'N' );
	$minutes = ( (int) $now->format( 'G' ) ) * 60 + (int) $now->format( 'i' );
	$status  = null;

	foreach ( $hours[ $today ] ?? array() as $interval ) {
		if ( $minutes >= $interval[0] && $minutes < $interval[1] ) {
			$status = array(
				'state' => 'open',
				/* translators: %s: closing time */
				'text'  => sprintf( __( 'Open today until %s', 'basalt-core' ), basalt_core_format_minutes( $interval[1] ) ),
			);
			break;
		}

		if ( $minutes < $interval[0] ) {
			$status = array(
				'state' => 'opens-later',
				/* translators: %s: opening time */
				'text'  => sprintf( __( 'Opens today at %s', 'basalt-core' ), basalt_core_format_minutes( $interval[0] ) ),
			);
			break;
		}
	}

	if ( null === $status ) {
		$status = array(
			'state' => 'closed',
			'text'  => __( 'Closed today', 'basalt-core' ),
		);

		for ( $offset = 1; $offset <= 7; $offset++ ) {
			$day = ( ( $today - 1 + $offset ) % 7 ) + 1;

			if ( ! empty( $hours[ $day ] ) ) {
				$status['text'] = sprintf(
					/* translators: 1: weekday name, 2: opening time */
					__( 'Closed, opens %1$s at %2$s', 'basalt-core' ),
					1 === $offset ? __( 'tomorrow', 'basalt-core' ) : basalt_core_weekday_name( $day ),
					basalt_core_format_minutes( $hours[ $day ][0][0] )
				);
				break;
			}
		}
	}

	/**
	 * Filter the opening status.
	 *
	 * @param array{state: string, text: string} $status The status.
	 * @param array<int, array<int, array{0: int, 1: int}>> $hours  Parsed hours.
	 */
	return (array) apply_filters( 'basalt_core_opening_status', $status, $hours );
}

/**
 * Localised weekday name for a Monday-first weekday number.
 *
 * @param int  $day   1 (Monday) to 7 (Sunday).
 * @param bool $short Abbreviated form.
 * @return string
 */
function basalt_core_weekday_name( int $day, bool $short = false ): string {
	global $wp_locale;

	$name = $wp_locale->get_weekday( $day % 7 );

	return $short ? $wp_locale->get_weekday_abbrev( $name ) : $name;
}

/**
 * Rows for the table.
 *
 * Consecutive days that open at the same time form a run, and the run is one
 * row: "Tuesday to Friday, 10:00 to 18:00". A day inside the run that stays
 * open longer gets a short row of its own, "Thursday until 20:00", which is
 * how a shop writes its hours on the door. A day that closes earlier, opens at
 * another time or has a break starts a new run, and an extension only joins a
 * run that already has two days with the base hours, so a single odd day at
 * the start of the week does not drag the rest into exceptions. Closed days
 * are grouped among themselves.
 *
 * @param array<int, array<int, array{0: int, 1: int}>> $hours       Parsed hours.
 * @param bool                                          $closed_days Whether to list closed days as a row.
 * @param bool                                          $short       Abbreviated weekday names.
 * @return array<int, array{days: string, hours: string, closed: bool}>
 */
function basalt_core_opening_rows( array $hours, bool $closed_days = true, bool $short = false ): array {
	$start = (int) get_option( 'start_of_week', 1 );
	$start = 0 === $start ? 7 : $start;
	$runs  = array();

	for ( $i = 0; $i < 7; $i++ ) {
		$day       = ( ( $start - 1 + $i ) % 7 ) + 1;
		$intervals = $hours[ $day ] ?? array();
		$last      = count( $runs ) - 1;
		$run       = $last >= 0 ? $runs[ $last ] : null;

		if ( ! $intervals ) {
			if ( $run && $run['closed'] ) {
				$runs[ $last ]['days'][] = $day;
			} else {
				$runs[] = array( 'closed' => true, 'days' => array( $day ), 'intervals' => array(), 'extended' => array() );
			}
			continue;
		}

		$single = 1 === count( $intervals );

		if ( $run && ! $run['closed'] ) {
			if ( $run['intervals'] === $intervals ) {
				$runs[ $last ]['days'][] = $day;
				continue;
			}

			$base_days = count( $run['days'] ) - count( $run['extended'] );

			if ( $single && 1 === count( $run['intervals'] ) && $intervals[0][0] === $run['intervals'][0][0] && $intervals[0][1] > $run['intervals'][0][1] && $base_days >= 2 ) {
				$runs[ $last ]['days'][]           = $day;
				$runs[ $last ]['extended'][ $day ] = $intervals[0][1];
				continue;
			}
		}

		$runs[] = array( 'closed' => false, 'days' => array( $day ), 'intervals' => $intervals, 'extended' => array() );
	}

	// The week wraps: a closed Sunday and a closed Monday are one row, "Sunday, Monday".
	if ( count( $runs ) > 1 && $runs[0]['closed'] && $runs[ count( $runs ) - 1 ]['closed'] ) {
		$runs[ count( $runs ) - 1 ]['days'] = array_merge( $runs[ count( $runs ) - 1 ]['days'], $runs[0]['days'] );
		array_shift( $runs );
	}

	$rows = array();

	foreach ( $runs as $run ) {
		if ( $run['closed'] && ! $closed_days ) {
			continue;
		}

		$spans = array();

		foreach ( $run['intervals'] as $interval ) {
			/* translators: 1: opening time, 2: closing time */
			$spans[] = sprintf( _x( '%1$s to %2$s', 'time range', 'basalt-core' ), basalt_core_format_minutes( $interval[0] ), basalt_core_format_minutes( $interval[1] ) );
		}

		$rows[] = array(
			'days'   => basalt_core_day_label( $run['days'], $short ),
			'hours'  => $run['closed'] ? __( 'Closed', 'basalt-core' ) : implode( _x( ', ', 'time range separator', 'basalt-core' ), $spans ),
			'closed' => $run['closed'],
		);

		foreach ( $run['extended'] as $day => $close ) {
			$rows[] = array(
				'days'   => basalt_core_weekday_name( (int) $day, $short ),
				/* translators: %s: closing time */
				'hours'  => sprintf( _x( 'until %s', 'closing time only', 'basalt-core' ), basalt_core_format_minutes( (int) $close ) ),
				'closed' => false,
			);
		}
	}

	/**
	 * Filter the rows of the opening hours table.
	 *
	 * @param array<int, array{days: string, hours: string, closed: bool}> $rows  The rows.
	 * @param array<int, array<int, array{0: int, 1: int}>>          $hours Parsed hours.
	 */
	return (array) apply_filters( 'basalt_core_opening_rows', $rows, $hours );
}

/**
 * "Tuesday to Friday" for three or more consecutive days, a list otherwise.
 *
 * @param int[] $days  Weekday numbers, in order.
 * @param bool  $short Abbreviated names.
 * @return string
 */
function basalt_core_day_label( array $days, bool $short ): string {
	$names = array_map( static fn( int $day ): string => basalt_core_weekday_name( $day, $short ), $days );
	$count = count( $names );

	if ( $count > 2 ) {
		/* translators: 1: first weekday, 2: last weekday */
		return sprintf( _x( '%1$s to %2$s', 'weekday range', 'basalt-core' ), $names[0], $names[ $count - 1 ] );
	}

	return implode( _x( ', ', 'weekday list separator', 'basalt-core' ), $names );
}

/**
 * Render the opening hours block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_core_opening_hours_block( $attributes ): string {
	$attributes = wp_parse_args(
		(array) $attributes,
		array(
			'showStatus'     => true,
			'showTable'      => true,
			'showClosedDays' => true,
			'layout'         => 'stacked',
		)
	);

	$hours = basalt_core_opening_hours();

	if ( ! array_filter( $hours ) ) {
		return '';
	}

	$out = '';

	if ( $attributes['showStatus'] ) {
		$status = basalt_core_opening_status( $hours );
		$out   .= sprintf(
			'<p class="basalt-opening-hours__status is-%1$s">%2$s</p>',
			esc_attr( $status['state'] ),
			esc_html( $status['text'] )
		);
	}

	if ( $attributes['showTable'] && 'inline' === $attributes['layout'] ) {
		/*
		 * One line, short day names, for a header strip: "Tue to Fri 10:00 to
		 * 18:00 · Sat 10:00 to 14:00". The separator is hidden from assistive
		 * technology; each row is its own span, which reads as a list anyway.
		 */
		$items = array();

		foreach ( basalt_core_opening_rows( $hours, (bool) $attributes['showClosedDays'], true ) as $row ) {
			$items[] = sprintf( '<span class="basalt-opening-hours__item%3$s">%1$s %2$s</span>', esc_html( $row['days'] ), esc_html( $row['hours'] ), $row['closed'] ? ' is-closed' : '' );
		}

		$out .= '<p class="basalt-opening-hours__inline">' . implode( '<span class="basalt-opening-hours__sep" aria-hidden="true"> · </span>', $items ) . '</p>';
	} elseif ( $attributes['showTable'] ) {
		$rows = '';

		foreach ( basalt_core_opening_rows( $hours, (bool) $attributes['showClosedDays'] ) as $row ) {
			$rows .= sprintf(
				'<div class="basalt-opening-hours__row%3$s"><dt>%1$s</dt><dd>%2$s</dd></div>',
				esc_html( $row['days'] ),
				esc_html( $row['hours'] ),
				$row['closed'] ? ' is-closed' : ''
			);
		}

		$out .= '<dl class="basalt-opening-hours__table">' . $rows . '</dl>';
	}

	if ( '' === $out ) {
		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes( array( 'class' => 'basalt-opening-hours' ) ),
		$out
	);
}
