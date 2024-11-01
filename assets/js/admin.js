( function ( $ ) {

    $( document ).ready( function () {
		// initialize color picker
		$( '.ultimate_notice_color' ).wpColorPicker();

		// refuse option
		$( '#ultimate_notice_refuse_opt' ).change( function () {
			if ( $( this ).is( ':checked' ) ) {
				$( '#ultimate_notice_refuse_opt_container' ).slideDown( 'fast' );
			} else {
				$( '#ultimate_notice_refuse_opt_container' ).slideUp( 'fast' );
			}
		} );
		
		// revoke option
		$( '#ultimate_notice_revoke_cookies' ).change( function () {
			if ( $( this ).is( ':checked' ) ) {
				$( '#ultimate_notice_revoke_opt_container' ).slideDown( 'fast' );
			} else {
				$( '#ultimate_notice_revoke_opt_container' ).slideUp( 'fast' );
			}
		} );

		// privacy policy option
		$( '#ultimate_notice_see_more' ).change( function () {
			if ( $( this ).is( ':checked' ) ) {
				$( '#ultimate_notice_see_more_opt' ).slideDown( 'fast' );
			} else {
				$( '#ultimate_notice_see_more_opt' ).slideUp( 'fast' );
			}
		} );

		// privacy policy option
		$( '#ultimate_notice_on_scroll' ).change( function () {
			if ( $( this ).is( ':checked' ) ) {
				$( '#ultimate_notice_on_scroll_offset' ).slideDown( 'fast' );
			} else {
				$( '#ultimate_notice_on_scroll_offset' ).slideUp( 'fast' );
			}
		} );

		// privacy policy link
		$( '#ultimate_notice_see_more_link-custom, #ultimate_notice_see_more_link-page' ).change( function () {
			if ( $( '#ultimate_notice_see_more_link-custom:checked' ).val() === 'custom' ) {
				$( '#ultimate_notice_see_more_opt_page' ).slideUp( 'fast', function () {
					$( '#ultimate_notice_see_more_opt_link' ).slideDown( 'fast' );
				} );
			} else if ( $( '#ultimate_notice_see_more_link-page:checked' ).val() === 'page' ) {
				$( '#ultimate_notice_see_more_opt_link' ).slideUp( 'fast', function () {
					$( '#ultimate_notice_see_more_opt_page' ).slideDown( 'fast' );
				} );
			}
		} );
		
		$( '#ultimate_notice_refuse_code_fields' ).find( 'a' ).click( function ( e ) {
			e.preventDefault();

			$( '#ultimate_notice_refuse_code_fields' ).find( 'a' ).removeClass( 'nav-tab-active' );
			$( '.refuse-code-tab' ).removeClass( 'active' );

			var id = $( this ).attr( 'id' ).replace( '-tab', '' );

			$( '#' + id ).addClass( 'active' );
			$( this ).addClass( 'nav-tab-active' );
		} );
    } );

	$( document ).on( 'click', 'input#reset_ultimate_notice_options', function () {
		return confirm( cnArgs.resetToDefaults );
	} );

} )( jQuery );