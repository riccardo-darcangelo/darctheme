/**
 * Editor UI for the breadcrumbs block.
 *
 * Written against the wp globals with no JSX, so the plugin ships without a
 * build step. ServerSideRender shows the real trail for the post being edited
 * rather than a placeholder, which matters here: the trail depends on the post
 * type and its taxonomy, so a static preview would be a guess.
 *
 * @package BasaltCore
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/breadcrumbs', {
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
						{ title: __( 'Breadcrumbs', 'basalt-core' ) },
						el( components.ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Let an SEO plugin render the trail', 'basalt-core' ),
							help: __(
								'When Rank Math or Yoast is active, use its breadcrumb instead of this one so the page carries only one BreadcrumbList.',
								'basalt-core'
							),
							checked: !! props.attributes.deferToSeoPlugin,
							onChange: function ( value ) {
								props.setAttributes( { deferToSeoPlugin: value } );
							},
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( serverSideRender, {
						block: 'basalt/breadcrumbs',
						attributes: props.attributes,
						EmptyResponsePlaceholder: function () {
							return el(
								components.Placeholder,
								{ label: __( 'Breadcrumbs', 'basalt-core' ) },
								__(
									'Nothing to show here. The front page is the root of the trail, so it has none.',
									'basalt-core'
								)
							);
						},
					} )
				)
			);
		},

		// Rendered on the server; nothing is stored in post content.
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
