/**
 * Editor UI for the attribute range block.
 *
 * @package BasaltShop
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/product-attribute-range', {
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
						{ title: __( 'Attribute range', 'basalt-shop' ) },
						el( components.TextControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Attributes', 'basalt-shop' ),
							help: __( 'Attribute slugs, comma separated, in the order to show them. Global attributes with or without the pa_ prefix, for example: unterbrust, cup', 'basalt-shop' ),
							value: props.attributes.attributes || '',
							onChange: function ( value ) {
								props.setAttributes( { attributes: value } );
							},
						} ),
						el( components.ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Label on the first attribute too', 'basalt-shop' ),
							help: __( 'Off by default: "70 to 90 · Cup B to G" reads better than "Band 70 to 90 · Cup B to G".', 'basalt-shop' ),
							checked: !! props.attributes.labelFirst,
							onChange: function ( value ) {
								props.setAttributes( { labelFirst: value } );
							},
						} ),
						el( components.TextControl, {
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							label: __( 'Separator', 'basalt-shop' ),
							value: props.attributes.separator,
							onChange: function ( value ) {
								props.setAttributes( { separator: value } );
							},
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( serverSideRender, {
						block: 'basalt/product-attribute-range',
						attributes: props.attributes,
						urlQueryArgs: { postId: props.context.postId },
						EmptyResponsePlaceholder: function () {
							return el(
								components.Placeholder,
								{ label: __( 'Attribute range', 'basalt-shop' ) },
								__( 'Shows the span of the chosen attributes for each product. Nothing to preview until an attribute slug is entered and a product is in context.', 'basalt-shop' )
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
