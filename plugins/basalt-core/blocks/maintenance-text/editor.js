/**
 * Editor UI for the maintenance text block.
 *
 * The text itself is not edited here. It lives in the settings, so somebody
 * can change "back at 18:00" from a phone without opening the site editor,
 * which is the situation this page exists for.
 *
 * @package BasaltCore
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/maintenance-text', {
		edit: function ( props ) {
			var blockProps = blockEditor.useBlockProps();
			var a = props.attributes;

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Maintenance text', 'basalt-core' ) },
						el( components.SelectControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Which text', 'basalt-core' ),
							value: a.field,
							options: [
								{ label: __( 'Headline', 'basalt-core' ), value: 'headline' },
								{ label: __( 'Message', 'basalt-core' ), value: 'message' },
								{ label: __( 'Back at, time', 'basalt-core' ), value: 'until' },
							],
							onChange: function ( value ) {
								props.setAttributes( { field: value } );
							},
						} ),
						el( components.SelectControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Render as', 'basalt-core' ),
							value: String( a.level ),
							options: [
								{ label: __( 'Paragraph', 'basalt-core' ), value: '0' },
								{ label: __( 'Heading 1', 'basalt-core' ), value: '1' },
								{ label: __( 'Heading 2', 'basalt-core' ), value: '2' },
								{ label: __( 'Heading 3', 'basalt-core' ), value: '3' },
							],
							onChange: function ( value ) {
								props.setAttributes( { level: parseInt( value, 10 ) } );
							},
						} ),
						el( components.TextControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Text before', 'basalt-core' ),
							help: __( 'Optional, for example "back at ".', 'basalt-core' ),
							value: a.prefix || '',
							onChange: function ( value ) {
								props.setAttributes( { prefix: value } );
							},
						} ),
						el( components.TextControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Text after', 'basalt-core' ),
							value: a.suffix || '',
							onChange: function ( value ) {
								props.setAttributes( { suffix: value } );
							},
						} ),
						el(
							'p',
							{ className: 'components-base-control__help' },
							__( 'The wording is edited under Settings, Search and schema, Maintenance mode.', 'basalt-core' )
						)
					)
				),
				el(
					'div',
					blockProps,
					el( serverSideRender, {
						block: 'basalt/maintenance-text',
						attributes: a,
						EmptyResponsePlaceholder: function () {
							return el(
								components.Placeholder,
								{ label: __( 'Maintenance text', 'basalt-core' ) },
								__( 'Nothing entered for this text yet. Fill it in under Settings, Search and schema, Maintenance mode.', 'basalt-core' )
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
