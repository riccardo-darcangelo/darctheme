/**
 * Editor UI for the form block.
 *
 * Every visible string is an attribute, so a site writes its labels in its
 * own language here. The preview is the server render, which is also what a
 * visitor gets, minus the submission handling.
 *
 * @package BasaltForms
 */

( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	function text( props, key, label, help ) {
		return el( components.TextControl, {
			__nextHasNoMarginBottom: true,
			__next40pxDefaultSize: true,
			label: label,
			help: help,
			value: props.attributes[ key ] || '',
			onChange: function ( value ) {
				var next = {};
				next[ key ] = value;
				props.setAttributes( next );
			},
		} );
	}

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

	blocks.registerBlockType( 'basalt/form', {
		edit: function ( props ) {
			var blockProps = blockEditor.useBlockProps();
			var a = props.attributes;

			// A stable id per form, so two forms on one page stay apart and the
			// success marker in the URL finds the right one.
			element.useEffect( function () {
				if ( ! a.formId ) {
					props.setAttributes( { formId: props.clientId.replace( /-/g, '' ).slice( 0, 8 ) } );
				}
			}, [] );

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Delivery', 'basalt-forms' ) },
						text( props, 'recipient', __( 'Send to', 'basalt-forms' ), __( 'Leave empty for the site administration address.', 'basalt-forms' ) ),
						text( props, 'subject', __( 'Email subject', 'basalt-forms' ), __( '%name% and %site% are replaced.', 'basalt-forms' ) ),
						text( props, 'successMessage', __( 'Message after sending', 'basalt-forms' ) )
					),
					el(
						components.PanelBody,
						{ title: __( 'Fields', 'basalt-forms' ) },
						text( props, 'nameLabel', __( 'Name label', 'basalt-forms' ) ),
						text( props, 'emailLabel', __( 'Email label', 'basalt-forms' ) ),
						toggle( props, 'showPhone', __( 'Ask for a phone number', 'basalt-forms' ) ),
						a.showPhone ? text( props, 'phoneLabel', __( 'Phone label', 'basalt-forms' ) ) : null,
						toggle( props, 'showTopic', __( 'Ask what it is about', 'basalt-forms' ) ),
						a.showTopic ? text( props, 'topicLabel', __( 'Topic label', 'basalt-forms' ) ) : null,
						a.showTopic
							? el( components.TextareaControl, {
									__nextHasNoMarginBottom: true,
									label: __( 'Topic options, one per line', 'basalt-forms' ),
									value: a.topics || '',
									onChange: function ( value ) {
										props.setAttributes( { topics: value } );
									},
							  } )
							: null,
						toggle( props, 'showDate', __( 'Ask for a preferred date', 'basalt-forms' ) ),
						a.showDate ? text( props, 'dateLabel', __( 'Date label', 'basalt-forms' ) ) : null,
						toggle( props, 'showMessage', __( 'Message field', 'basalt-forms' ) ),
						a.showMessage ? text( props, 'messageLabel', __( 'Message label', 'basalt-forms' ) ) : null,
						a.showMessage ? toggle( props, 'messageRequired', __( 'Message is required', 'basalt-forms' ) ) : null,
						text( props, 'consentText', __( 'Consent text', 'basalt-forms' ), __( 'Always required. A link is allowed, for example <a href="/privacy/">privacy policy</a>.', 'basalt-forms' ) ),
						text( props, 'submitLabel', __( 'Button label', 'basalt-forms' ) )
					)
				),
				el(
					'div',
					blockProps,
					a.formId
						? el( serverSideRender, {
								block: 'basalt/form',
								attributes: a,
						  } )
						: el( components.Spinner )
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
