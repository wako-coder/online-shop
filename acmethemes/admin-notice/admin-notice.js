/**
 * Getting Started
 */
jQuery( document ).ready( function ( $ ) {

	$( '.at-gsm-btn' ).click( function ( e ) {
		e.preventDefault();

		$( this ).addClass( 'updating-message' );
		$( this ).text( online_shop_adi_install.btn_text );

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: {
				action     : 'at_getting_started',
				security : online_shop_adi_install.nonce,
				slug : 'advanced-import',
				request : 1
			},
			success:function( response ) {
				setTimeout(function(){
					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: {
							action     : 'at_getting_started',
							security : online_shop_adi_install.nonce,
							slug : 'acme-demo-setup',
							request : 2
						},
						success:function( response ) {
							var extra_uri, redirect_uri, dismiss_nonce;
							redirect_uri         = online_shop_adi_install.adminurl+'/themes.php?page=advanced-import&browse=all&at-gsm-hide-notice=welcome';
							if ( $( '.at-gsm-close' ).length ) {
								dismiss_nonce = $( '.at-gsm-close' ).attr( 'href' ).split( 'at_gsm_admin_notice_nonce=' )[1];
								extra_uri     = '&at_gsm_admin_notice_nonce=' + dismiss_nonce;
							}
							redirect_uri         = redirect_uri + extra_uri;
							window.location.href = redirect_uri;

						},
						error: function( xhr, ajaxOptions, thrownError ){
							console.log(thrownError);
						}
					});
				}, 2000);
			},
			error: function( xhr, ajaxOptions, thrownError ){
				console.log(thrownError);
			}
		});
	} );
} );