<?php
/**
 * Colour arithmetic.
 *
 * Shared because more than one thing needs it: the login screen checks the
 * colours an administrator picked, and the plugin corrections check colours a
 * third party plugin picked. Both are asking the same question, which is
 * whether a person can read the result.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;


/**
 * Validate a hex colour.
 *
 * Implemented here rather than with sanitize_hex_color(), which is not loaded
 * on every request. Returns an empty string for anything that is not a plain
 * three or six digit hex colour, so nothing else can reach the stylesheet.
 *
 * @param mixed $value Raw value.
 * @return string A #rrggbb value, or an empty string.
 */
function basalt_core_hex_color( $value ): string {
	$value = trim( (string) $value );

	return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value ) ? $value : '';
}

/**
 * Relative luminance of a hex colour, per WCAG.
 *
 * @param string $hex A #rgb or #rrggbb value.
 * @return float Between 0 and 1.
 */
function basalt_core_luminance( string $hex ): float {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	$channels = array_map(
		static function ( string $pair ): float {
			$value = hexdec( $pair ) / 255;

			return $value <= 0.03928 ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
		},
		str_split( $hex, 2 )
	);

	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * Contrast ratio between two hex colours.
 *
 * @param string $one First colour.
 * @param string $two Second colour.
 * @return float Between 1 and 21.
 */
function basalt_core_contrast_ratio( string $one, string $two ): float {
	$a = basalt_core_luminance( $one );
	$b = basalt_core_luminance( $two );

	return ( max( $a, $b ) + 0.05 ) / ( min( $a, $b ) + 0.05 );
}

/**
 * The better of black or white to put on a background.
 *
 * Two candidates rather than a computed value on purpose: near black and near
 * white are the only two colours guaranteed to be available whatever the
 * background turns out to be, and picking the better of them is what keeps
 * text readable on a colour nobody has seen yet.
 *
 * @param string $background A #rgb or #rrggbb value.
 * @return string A #rrggbb value.
 */
function basalt_core_readable_on( string $background ): string {
	return basalt_core_contrast_ratio( $background, '#000000' ) >= basalt_core_contrast_ratio( $background, '#ffffff' )
		? '#16191d'
		: '#f2f4f5';
}
