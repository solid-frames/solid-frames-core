<?php
/**
 * Plugin Name: Solid Frames Core
 * Description: Globale Sicherheits- und Performance-Standards (MU-Plugin).
 * Version: 1.0.7
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
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}
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

add_action( 'template_redirect', function() {
	if ( is_author() ) {
		wp_redirect( home_url(), 301 );
		exit;
	}
} );

add_filter( 'wp_is_application_passwords_available', '__return_false' );

// Login-Fehler generalisieren (Nur beim echten Login-Vorgang)
add_filter( 'login_errors', function( $error ) {
	if ( ! isset( $_GET['action'] ) || $_GET['action'] === 'login' ) {
		return '<strong>FEHLER</strong>: Die Eingaben sind nicht korrekt.';
	}
	return $error;
} );

// ==============================================================================
// PASSWORT-RESET ABSICHERN (Ihre Lösung)
// ==============================================================================

// 1. Umleitung bei Fehlern (Erfolgreiche Resets leiten schon vorher um)
add_action( 'lost_password', function() {
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		wp_redirect( wp_login_url() . '?checkemail=confirm' );
		exit;
	}
} );

// 2. Standard-Nachricht auf der Bestätigungsseite überschreiben
add_filter( 'login_message', function( $message ) {
	if ( isset( $_GET['checkemail'] ) && 'confirm' === $_GET['checkemail'] ) {
		$custom_text = 'Sofern ein Konto mit diesen Angaben existiert, wird in Kürze eine E-Mail zum Zurücksetzen des Passworts versendet.';
		return '<p class="message">' . esc_html( $custom_text ) . '</p>';
	}
	return $message;
} );

// ==============================================================================

function sf_enforce_password_security( $errors, $user ) {
	$password = ( isset( $_POST['pass1'] ) && trim( $_POST['pass1'] ) ) ? $_POST['pass1'] : null;

	if ( ! $password ) {
		return $errors;
	}

	if ( strlen( $password ) < 12 ) {
		$errors->add( 'pass', '<strong>FEHLER</strong>: Das Passwort muss mindestens 12 Zeichen lang sein.' );
	}
	if ( ! preg_match( "/[a-z]/", $password ) || ! preg_match( "/[A-Z]/", $password ) ) {
		$errors->add( 'pass', '<strong>FEHLER</strong>: Das Passwort muss Groß- und Kleinbuchstaben enthalten.' );
	}
	if ( ! preg_match( "/[0-9]/", $password ) ) {
		$errors->add( 'pass', '<strong>FEHLER</strong>: Das Passwort muss mindestens eine Zahl enthalten.' );
	}

	return $errors;
}
add_action( 'user_profile_update_errors', 'sf_enforce_password_security', 10, 2 );
add_action( 'validate_password_reset', 'sf_enforce_password_security', 10, 2 );


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
