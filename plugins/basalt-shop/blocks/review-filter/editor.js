/**
 * Editor UI for the review filter.
 *
 * @package BasaltShop
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/review-filter', {
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
						{ title: __( 'Review filter', 'basalt-shop' ) },
						el( components.ToggleControl, {
							label: __( 'Also filter by how the size came out', 'basalt-shop' ),
							checked: attributes.showFit,
							onChange: function ( value ) {
								setAttributes( { showFit: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.TextControl, {
							label: __( 'Label for the sizes', 'basalt-shop' ),
							value: attributes.sizeLabel,
							onChange: function ( value ) {
								setAttributes( { sizeLabel: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.TextControl, {
							label: __( 'Label for the fit', 'basalt-shop' ),
							value: attributes.fitLabel,
							onChange: function ( value ) {
								setAttributes( { fitLabel: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el(
					'div',
					blockEditor.useBlockProps(),
					el( serverSideRender, {
						block: 'basalt/review-filter',
						attributes: attributes,
						urlQueryArgs: { postId: props.context.postId },
						EmptyResponsePlaceholder: function () {
							return el(
								components.Placeholder,
								{ label: __( 'Review filter', 'basalt-shop' ) },
								__( 'Appears once reviews have named two different sizes.', 'basalt-shop' )
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
