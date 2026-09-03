/**
 * Editor UI for the fit summary.
 *
 * Nothing to configure: the block shows what the reviews say.
 *
 * @package BasaltShop
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/fit-summary', {
		edit: function ( props ) {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( serverSideRender, {
					block: 'basalt/fit-summary',
					attributes: props.attributes,
					urlQueryArgs: { postId: props.context.postId },
					EmptyResponsePlaceholder: function () {
						return el(
							components.Placeholder,
							{ label: __( 'Fit summary', 'basalt-shop' ) },
							__( 'Appears once a review has answered the fit question.', 'basalt-shop' )
						);
					},
				} )
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
