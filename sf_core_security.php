<?php
/**
 * Plugin Name: Solid Frames Core
 * Description: Globale Sicherheits- und Performance-Standards (MU-Plugin).
 * Version: 1.0.9
 * Author: Solid Frames
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direkten Dateizugriff blockieren
}

// ==============================================================================
// 1. FUNKTIONALITÄT & WORKFLOW
// ==============================================================================

add_filter( 'upload_mimes', function( $mimes ) {
	$mimes['vcf'] = 'text/vcard';
	return $mimes;
} );

add_filter( 'admin_email_check_interval', '__return_false' );
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );
add_filter( 'auto_core_update_send_email', '__return_false' );

add_action( 'init', function() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
} );


// ==============================================================================
// 2. SICHERHEIT & HARDENING
// ==============================================================================

add_filter( 'the_generator', '__return_empty_string' );
add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter( 'wp_headers', function( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
});

add_filter( 'rest_endpoints', function( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		if ( isset( $endpoints['/wp/v2/users'] ) ) unset( $endpoints['/wp/v2/users'] );
		if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
});

// Priorität 1: muss vor Cores redirect_canonical (Prio 10) laufen, sonst steht
// der Nicename schon im Location-Header von dessen /?author=1-Redirect.
add_action( 'template_redirect', function() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );

add_filter( 'oembed_response_data', function( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
} );

add_filter( 'wp_sitemaps_add_provider', function( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

add_filter( 'wp_is_application_passwords_available', '__return_false' );

// Zugangsdaten-Fehler vereinheitlichen (Login, WooCommerce, XML-RPC – alles was
// wp_authenticate() nutzt). Andere Meldungen (Passwort-Richtlinie, Cookies) bleiben sichtbar.
add_filter( 'authenticate', function( $user ) {
	if ( is_wp_error( $user ) && array_intersect( $user->get_error_codes(), [ 'invalid_username', 'invalid_email', 'incorrect_password' ] ) ) {
		return new WP_Error( 'sf_invalid_login', '<strong>FEHLER</strong>: Die Eingaben sind nicht korrekt.' );
	}
	return $user;
}, 100 );

// ==============================================================================
// PASSWORT-RESET ABSICHERN (Ihre Lösung)
// ==============================================================================

// 1. Umleitung bei Fehlern (Erfolgreiche Resets leiten schon vorher um).
// $user_data (seit WP 5.4) zeigt zuverlässig, ob der Nutzer gefunden wurde -
// Core setzt bei unbekanntem Benutzernamen den Code 'invalidcombo', nicht
// 'invalid_username', daher genügt eine Prüfung auf $user_data.
add_action( 'lostpassword_post', function( $errors, $user_data ) {
	if ( ! $user_data && ! in_array( 'empty_username', $errors->get_error_codes(), true ) ) {
		wp_safe_redirect( add_query_arg( 'checkemail', 'confirm', wp_login_url() ) );
		exit;
	}
}, 10, 2 );

// 2. Standard-Nachricht auf der Bestätigungsseite überschreiben
add_filter( 'login_message', function( $message ) {
	if ( isset( $_GET['checkemail'] ) && 'confirm' === $_GET['checkemail'] ) {
		$custom_text = 'Sofern ein Konto mit diesen Angaben existiert, wird in Kürze eine E-Mail zum Zurücksetzen des Passworts versendet.';
		return '<p class="message">' . esc_html( $custom_text ) . '</p>';
	}
	return $message;
} );

// ==============================================================================

// NIST 800-63B: Laenge schlaegt erzwungene Komplexitaet, daher nur Mindestlaenge pruefen.
function sf_password_too_short( $password ) {
	return mb_strlen( $password ) < 14;
}

// $update: user_profile_update_errors uebergibt hier ein bool (Update ja/nein),
// validate_password_reset uebergibt stattdessen $user - beides ungenutzt.
function sf_enforce_password_security( $errors, $update ) {
	$password = ( isset( $_POST['pass1'] ) && trim( wp_unslash( $_POST['pass1'] ) ) ) ? trim( wp_unslash( $_POST['pass1'] ) ) : null;

	if ( $password && sf_password_too_short( $password ) ) {
		$errors->add( 'pass', '<strong>FEHLER</strong>: Das Passwort muss mindestens 14 Zeichen lang sein.' );
	}

	return $errors;
}
add_action( 'user_profile_update_errors', 'sf_enforce_password_security', 10, 2 );
add_action( 'validate_password_reset', 'sf_enforce_password_security', 10, 2 );

// Gleiche Regel auch bei Passwortaenderung ueber die REST-API (/wp/v2/users/...) durchsetzen.
add_filter( 'rest_pre_insert_user', function( $prepared_user, $request ) {
	$password = $request->get_param( 'password' );
	if ( $password && sf_password_too_short( $password ) ) {
		return new WP_Error( 'sf_rest_password_policy', 'Das Passwort muss mindestens 14 Zeichen lang sein.', [ 'status' => 400 ] );
	}
	return $prepared_user;
}, 10, 2 );


// ==============================================================================
// 3. KOMMENTARE GLOBAL DEAKTIVIEREN
// ==============================================================================

add_action( 'admin_init', function () {
	global $pagenow;
	
	if ( $pagenow === 'edit-comments.php' ) {
		wp_safe_redirect( admin_url() );
		exit;
	}

	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
});

add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
});

add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'comments' );
}, 999 );
