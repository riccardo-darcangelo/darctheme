/**
 * Editor UI for the opening hours block.
 *
 * Three toggles and a server side preview. The preview is the real thing: it
 * reads the hours from the settings and computes today's status, so what the
 * editor sees is what a visitor sees at that moment.
 *
 * @package BasaltCore
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	function toggle( props, key, label, help ) {
		return el( components.ToggleControl, {
			__nextHasNoMarginBottom: true,
			label: label,
			help: help,
			checked: !! props.attributes[ key ],
			onChange: function ( value ) {
				var next = {};
				next[ key ] = value;
				props.setAttributes( next );
			},
		} );
	}

	blocks.registerBlockType( 'basalt/opening-hours', {
		edit: function ( props ) {
			var blockProps = blockEditor.useBlockProps();

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Opening hours', 'basalt-core' ) },
						toggle( props, 'showStatus', __( 'Show today\'s status', 'basalt-core' ), __( 'A line such as "Open today until 18:00", computed in the site\'s time zone.', 'basalt-core' ) ),
						toggle( props, 'showTable', __( 'Show the table', 'basalt-core' ) ),
						toggle( props, 'showClosedDays', __( 'List closed days', 'basalt-core' ), __( 'Adds a row for the days you are closed, so nobody has to infer it.', 'basalt-core' ) ),
						el( components.SelectControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Layout', 'basalt-core' ),
							value: props.attributes.layout || 'stacked',
							options: [
								{ label: __( 'Table, one row per group of days', 'basalt-core' ), value: 'stacked' },
								{ label: __( 'One line with short day names, for a header', 'basalt-core' ), value: 'inline' },
							],
							onChange: function ( value ) {
								props.setAttributes( { layout: value } );
							},
						} ),
						el(
							'p',
							{ className: 'components-base-control__help' },
							__( 'The hours themselves are edited under Settings, Search and schema.', 'basalt-core' )
						)
					)
				),
				el(
					'div',
					blockProps,
					el( serverSideRender, {
						block: 'basalt/opening-hours',
						attributes: props.attributes,
						EmptyResponsePlaceholder: function () {
							return el(
								components.Placeholder,
								{ label: __( 'Opening hours', 'basalt-core' ) },
								__( 'No opening hours entered yet. Add them under Settings, Search and schema, one rule per line, for example Mo-Fr 10:00-18:00.', 'basalt-core' )
							);
						},
					} )
				)
			);
		},

		save: function () {
			return null;
		},
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
) );
