/* global bp, BP_Mentions_Options */

window.bp = window.bp || {};

( function( bp, $, undefined ) {
	var mentionsQueryCache = [],
		mentionsItem;

	bp.mentions       = bp.mentions || {};
	bp.mentions.users = window.bp.mentions.users || [];

	if ( typeof window.BP_Suggestions === 'object' ) {
		bp.mentions.users = window.BP_Suggestions.friends || bp.mentions.users;
	}

	/**
	 * Adds BuddyPress @mentions to form inputs.
	 *
	 * @param {array|object} options If array, becomes the suggestions' data source. If object, passed as config to $.atwho().
	 * @since BuddyPress 2.1.0
	 */
	$.fn.bp_mentions = function( options, suggestions, mentions ) {
		if ( $.isArray( options ) ) {
			options = { data: options };
		}

		if ( ! suggestions ) {
			suggestions = {};
		}

		if ( ! mentions ) {
			mentions = {};
		}

		/**
		 * Default options for at.js; see https://github.com/ichord/At.js/.
		 */
		var suggestionsDefaults = $.extend( true, {}, {
			delay:             200,
			hideWithoutSuffix: true,
			insertTpl:         BP_Mentions_Options.insert_tpl,
			limit:             10,
			startWithSpace:    false,
			suffix:            '',

			callbacks: {
				/**
				 * Custom filter to only match the start of spaced words.
				 * Based on the core/default one.
				 *
				 * @param {string} query
				 * @param {array} data
				 * @param {string} search_key
				 * @return {array}
				 * @since BuddyPress 2.1.0
				 */
				filter: function( query, data, search_key ) {
					var item, _i, _len, _results = [],
					regxp = new RegExp( '^' + query + '| ' + query, 'ig' ); // start of string, or preceded by a space.

					for ( _i = 0, _len = data.length; _i < _len; _i++ ) {
						item = data[ _i ];
						if ( item[ search_key ].toLowerCase().match( regxp ) ) {
							_results.push( item );
						}
					}

					return _results;
				},

				/**
				 * Removes some spaces around highlighted string and tweaks regex to allow spaces
				 * (to match display_name). Based on the core default.
				 *
				 * @param {unknown} li
				 * @param {string} query
				 * @return {string}
				 * @since BuddyPress 2.1.0
				 */
				highlighter: function( li, query ) {
					if ( ! query ) {
						return li;
					}

					var regexp = new RegExp( '>(\\s*|[\\w\\s]*)(' + this.at.replace( '+', '\\+') + '?' + query.replace( '+', '\\+' ) + ')([\\w ]*)\\s*<', 'ig' );
					return li.replace( regexp, function( str, $1, $2, $3 ) {
						return '>' + $1 + '<strong>' + $2 + '</strong>' + $3 + '<';
					});
				},

				/**
				 * Reposition the suggestion list dynamically.
				 *
				 * @param {unknown} offset
				 * @since BuddyPress 2.1.0
				 */
				before_reposition: function( offset ) {
					// get the iframe, if any, already applied with atwho
					var caret,
							line,
							iframeOffset,
							move,
							$view = $( '#atwho-ground-' + this.id + ' .atwho-view' ),
							$body = $( 'body' ),
							atwhoDataValue = this.$inputor.data( 'atwho' );

					if ( 'undefined' !== atwhoDataValue && 'undefined' !== atwhoDataValue.iframe && null !== atwhoDataValue.iframe ) {
						caret = this.$inputor.caret( 'offset', { iframe: atwhoDataValue.iframe } );
						// Caret.js no longer calculates iframe caret position from the window (it's now just within the iframe).
						// We need to get the iframe offset from the window and merge that into our object.
						iframeOffset = $( atwhoDataValue.iframe ).offset();
						if ( 'undefined' !== iframeOffset ) {
							caret.left += iframeOffset.left;
							caret.top  += iframeOffset.top;
						}
					} else {
						caret = this.$inputor.caret( 'offset' );
					}

					// If the caret is past horizontal half, then flip it, yo
					if ( caret.left > ( $body.width() / 2 ) ) {
						$view.addClass( 'right' );
						move = caret.left - offset.left - this.view.$el.width();
					} else {
						$view.removeClass( 'right' );
						move = caret.left - offset.left + 1;
					}

					// If we're on a small screen, scroll to caret
					if ( $body.width() <= 400 ) {
						$( document ).scrollTop( caret.top - 6 );
					}

					// New position is under the caret (never above) and positioned to follow
					// Dynamic sizing based on the input area (remove 'px' from end)
					line = parseInt( this.$inputor.css( 'line-height' ).substr( 0, this.$inputor.css( 'line-height' ).length - 2 ), 10 );
					if ( !line || line < 5 ) { // sanity check, and catch no line-height
						line = 19;
					}

					offset.top   = caret.top + line;
					offset.left += move;
				},

				/**
				 * Override default behaviour which inserts junk tags in the WordPress Visual editor.
				 *
				 * @param {unknown} $inputor Element which we're inserting content into.
				 * @param {string) content The content that will be inserted.
				 * @param {string) suffix Applied to the end of the content string.
				 * @return {string}
				 * @since BuddyPress 2.1.0
				 */
				inserting_wrapper: function( $inputor, content, suffix ) {
					return '' + content + suffix;
				}
			}
		}, suggestions );

		/**
		 * Default options for our @mentions; see https://github.com/ichord/At.js/.
		 */
		var mentionsDefaults = $.extend( true, {}, {
			callbacks: {
				/**
				 * If there are no matches for the query in this.data, then query BuddyPress.
				 *
				 * @param {string} query Partial @mention to search for.
				 * @param {function} render_view Render page callback function.
				 * @since BuddyPress 2.1.0
				 * @since BuddyPress 3.0.0. Renamed from "remote_filter" for at.js v1.5.4 support.
				 */
				remoteFilter: function( query, render_view ) {
					var self = $( this ),
						params = {};

					mentionsItem = mentionsQueryCache[ query ];
					if ( typeof mentionsItem === 'object' ) {
						render_view( mentionsItem );
						return;
					}

					if ( self.xhr ) {
						self.xhr.abort();
					}

					params = { 'action': 'bp_get_suggestions', 'term': query, 'type': 'members' };

					if ( typeof window.BP_Nouveau !== 'undefined' &&
						typeof window.BP_Nouveau.messages !== 'undefined' &&
						typeof window.BP_Nouveau.messages.force_friendship_to_message !== 'undefined' &&
						window.BP_Nouveau.messages.force_friendship_to_message ) {
						params.only_friends = 1;
					}

					if ( $.isNumeric( this.$inputor.data( 'suggestions-group-id' ) ) ) {
						params['group-id'] = parseInt( this.$inputor.data( 'suggestions-group-id' ), 10 );
					}

					self.xhr = $.getJSON( ajaxurl, params )
						/**
						 * Success callback for the @suggestions lookup.
						 *
						 * @param {object} response Details of users matching the query.
						 * @since BuddyPress 2.1.0
						 */
						.done(function( response ) {
							if ( ! response.success ) {
								return;
							}

							var data = $.map( response.data,
								/**
								 * Create a composite index to determine ordering of results;
								 * nicename matches will appear on top.
								 *
								 * @param {array} suggestion A suggestion's original data.
								 * @return {array} A suggestion's new data.
								 * @since BuddyPress 2.1.0
								 */
								function( suggestion ) {
									suggestion.search = suggestion.search || suggestion.ID + ' ' + suggestion.name;
									return suggestion;
								}
							);

							mentionsQueryCache[ query ] = data;
							render_view( data );
						});
				}
			},

			data: $.map( options.data,
				/**
				 * Create a composite index to search against of nicename + display name.
				 * This will also determine ordering of results, so nicename matches will appear on top.
				 *
				 * @param {array} suggestion A suggestion's original data.
				 * @return {array} A suggestion's new data.
				 * @since BuddyPress 2.1.0
				 */
				function( suggestion ) {
					suggestion.search = suggestion.search || suggestion.ID + ' ' + suggestion.name;
					return suggestion;
				}
			),

			at:         '@',
			searchKey:  'search',
			displayTpl: BP_Mentions_Options.display_tpl
		}, mentions );

		var opts = $.extend( true, {}, suggestionsDefaults, mentionsDefaults, options );
		return $.fn.atwho.call( this, opts );
	};

	$( document ).ready(function() {
        $(document).on('focus', BP_Mentions_Options.selectors.join(',') , function() {
            if ( $(this).data( 'bp_mentions_activated' ) ) {
                return;
            }

            if ( typeof( bp ) === 'undefined' ) {
                return;
            }

            $(this).bp_mentions( bp.mentions.users );
            $(this).data( 'bp_mentions_activated', true );
        });
	});

	bp.mentions.tinyMCEinit = function() {
		if ( typeof window.tinyMCE === 'undefined' || window.tinyMCE.activeEditor === null || typeof window.tinyMCE.activeEditor === 'undefined' ) {
			return;
		} else {
			$( window.tinyMCE.activeEditor.contentDocument.activeElement )
				.atwho( 'setIframe', $( '.wp-editor-wrap iframe' )[0] )
				.bp_mentions( bp.mentions.users );
		}
	};
})( bp, jQuery );
