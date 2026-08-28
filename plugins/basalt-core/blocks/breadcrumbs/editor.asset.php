<?php
/**
 * Script dependencies for the breadcrumbs editor script.
 *
 * Hand written because the plugin ships without a build step. Keep in sync with
 * the globals editor.js reads.
 *
 * @package BasaltCore
 */

return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-block-editor',
		'wp-components',
		'wp-server-side-render',
		'wp-i18n',
	),
	'version'      => '1.0.0',
);
