/**
 * Editor UI for the cart drawer.
 *
 * @package BasaltShop
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/cart-drawer', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Cart drawer', 'basalt-shop' ) },
						el( components.TextControl, {
							label: __( 'Label', 'basalt-shop' ),
							value: attributes.label,
							onChange: function ( value ) {
								setAttributes( { label: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.ToggleControl, {
							label: __( 'Show the number of items', 'basalt-shop' ),
							checked: attributes.showCount,
							onChange: function ( value ) {
								setAttributes( { showCount: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.ToggleControl, {
							label: __( 'Show the subtotal', 'basalt-shop' ),
							help: __( 'Useful in a wide header, too much in a narrow one.', 'basalt-shop' ),
							checked: attributes.showTotal,
							onChange: function ( value ) {
								setAttributes( { showTotal: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el(
							'p',
							{ style: { marginBottom: 0 } },
							__( 'The panel itself is only shown on the site. In the editor this is the button that opens it.', 'basalt-shop' )
						)
					)
				),
				el(
					'div',
					blockEditor.useBlockProps(),
					el( serverSideRender, {
						block: 'basalt/cart-drawer',
						attributes: attributes,
						EmptyResponsePlaceholder: function () {
							return el(
								components.Placeholder,
								{ label: __( 'Cart drawer', 'basalt-shop' ) },
								__( 'Appears once WooCommerce is active.', 'basalt-shop' )
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
