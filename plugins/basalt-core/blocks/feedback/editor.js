/**
 * Editor UI for the feedback block.
 *
 * @package BasaltCore
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'basalt/feedback', {
		edit: function ( props ) {
			var a = props.attributes;

			function text( key, label, help ) {
				return el( components.TextControl, {
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
					label: label,
					help: help,
					value: a[ key ] || '',
					onChange: function ( value ) {
						var next = {};
						next[ key ] = value;
						props.setAttributes( next );
					},
				} );
			}

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Feedback', 'basalt-core' ) },
						text( 'topic', __( 'Topic key', 'basalt-core' ), __( 'Groups the answers in the report. Empty uses the page slug.', 'basalt-core' ) ),
						text( 'question', __( 'Question', 'basalt-core' ) ),
						text( 'yesLabel', __( 'Yes button', 'basalt-core' ) ),
						text( 'noLabel', __( 'No button', 'basalt-core' ) ),
						text( 'moreLabel', __( 'Follow-up question after a no', 'basalt-core' ) ),
						text( 'sendLabel', __( 'Send button', 'basalt-core' ) ),
						text( 'callLabel', __( 'Second link after a no', 'basalt-core' ), __( 'Optional, for example "Rather call us".', 'basalt-core' ) ),
						text( 'callUrl', __( 'Where that link goes', 'basalt-core' ) )
					)
				),
				el(
					'div',
					blockEditor.useBlockProps(),
					el( serverSideRender, { block: 'basalt/feedback', attributes: a } )
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
