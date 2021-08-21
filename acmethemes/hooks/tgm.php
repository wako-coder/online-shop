<?php
add_action( 'tgmpa_register', 'online_shop_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
function online_shop_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
    // Include Acme Demo Setup as recommended
    $plugins = array(
        array(
            'name'      => 'Gutentor',
            'slug'      => 'gutentor',
            'required'  => false,
        )
    );
	tgmpa( $plugins );
}