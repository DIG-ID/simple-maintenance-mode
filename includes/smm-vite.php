<?php
/**
 * Vite integration: resolves compiled stylesheets from dist/.vite/manifest.json
 * and, during development, serves them from the Vite dev server with HMR.
 *
 * Entries are referenced by their source path, exactly as listed in
 * vite.config.mjs (e.g. 'public/sass/smm-public.sass'). Adapted from the
 * migrate-to-vite skill template; only stylesheets are built, so the script
 * and modulepreload helpers are left out.
 *
 * @package simple-maintenance-mode
 * @subpackage Helpers
 * @since 1.1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Returns the Vite dev server origin when it is running, or an empty string.
 *
 * The dev server writes its origin to dist/hot on start and removes it on
 * exit. The file is only honoured on local/development environments, so a
 * stray hot file can never make a live site load assets from localhost, and
 * only while the server actually answers — a hot file left behind by a killed
 * process falls back to the build instead of breaking the page.
 *
 * @since 1.1.0
 * @return string Dev server origin (e.g. 'https://testing.digid:5173') or ''.
 */
function smm_vite_dev_server() {
	static $origin = null;

	if ( null !== $origin ) {
		return $origin;
	}

	$origin   = '';
	$hot_file = SMM_DIR . 'dist/hot';

	if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) && file_exists( $hot_file ) ) {
		$contents = file_get_contents( $hot_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin file, not a remote request.
		$origin   = $contents ? untrailingslashit( trim( $contents ) ) : '';
	}

	if ( $origin ) {
		$host = wp_parse_url( $origin, PHP_URL_HOST );
		$port = wp_parse_url( $origin, PHP_URL_PORT );

		// Probe the IPv4 address: Local maps the host to ::1 first in the hosts
		// file, but the dev server only listens on 127.0.0.1.
		$ip = $host ? gethostbyname( $host ) : '';

		// Silenced: "connection refused" is the expected not-running case.
		$socket = ( $ip && $port ) ? @fsockopen( $ip, $port, $errno, $errstr, 0.1 ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fsockopen, WordPress.PHP.NoSilencedErrors.Discouraged -- Local TCP probe to the dev server.

		if ( $socket ) {
			fclose( $socket ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closes the probe socket.
		} else {
			$origin = '';
		}
	}

	return $origin;
}

/**
 * Returns the decoded Vite build manifest (empty array if missing).
 *
 * @since 1.1.0
 * @return array Manifest keyed by entry source path.
 */
function smm_vite_manifest() {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$manifest      = array();
	$manifest_path = SMM_DIR . 'dist/.vite/manifest.json';

	if ( file_exists( $manifest_path ) ) {
		$contents = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin file, not a remote request.
		$decoded  = $contents ? json_decode( $contents, true ) : null;

		if ( is_array( $decoded ) ) {
			$manifest = $decoded;
		}
	}

	return $manifest;
}

/**
 * Registers a script handle to be printed as an ES module, or returns the
 * list of registered module handles when called without arguments.
 *
 * @since 1.1.0
 *
 * @param string $handle Optional. Script handle to flag as module.
 * @return array List of module handles.
 */
function smm_vite_module_handle( $handle = '' ) {
	static $handles = array();

	if ( $handle ) {
		$handles[ $handle ] = true;
	}

	return array_keys( $handles );
}

/**
 * Enqueues the Vite HMR client (dev server only).
 *
 * @since 1.1.0
 * @return string Client script handle.
 */
function smm_vite_enqueue_client() {
	$handle = 'smm-vite-client';

	if ( ! wp_script_is( $handle, 'enqueued' ) ) {
		wp_enqueue_script( $handle, smm_vite_dev_server() . '/@vite/client', array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Served by the dev server, never cached.
		smm_vite_module_handle( $handle );
	}

	return $handle;
}

/**
 * Enqueues a Vite stylesheet entry (Sass/CSS).
 *
 * On the dev server the stylesheet is loaded as a module so it can be
 * hot-reloaded; in production it is a regular <link> to the hashed file.
 * Without a build (a git checkout before `npm run prod`) nothing is enqueued:
 * the page still works, unstyled by the plugin.
 *
 * @since 1.1.0
 *
 * @param string $handle Style handle.
 * @param string $entry  Entry source path, as in vite.config.mjs.
 * @param array  $deps   Optional. Style dependencies (production only).
 * @return bool True if the entry was enqueued.
 */
function smm_vite_enqueue_style( $handle, $entry, $deps = array() ) {
	$dev_server = smm_vite_dev_server();

	if ( $dev_server ) {
		$client = smm_vite_enqueue_client();
		wp_enqueue_script( $handle, $dev_server . '/' . $entry, array( $client ), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Served by the dev server, never cached.
		smm_vite_module_handle( $handle );

		return true;
	}

	$manifest = smm_vite_manifest();

	if ( empty( $manifest[ $entry ]['file'] ) ) {
		return false;
	}

	// File names carry a content hash, so no ?ver= query is needed.
	wp_enqueue_style( $handle, SMM_URL . 'dist/' . $manifest[ $entry ]['file'], $deps, null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Cache-busting via hashed file name.

	return true;
}

/**
 * Prints the plugin's Vite scripts as ES modules (type="module").
 *
 * @since 1.1.0
 *
 * @param string $tag    The <script> tag.
 * @param string $handle Script handle.
 * @return string
 */
function smm_vite_module_tag( $tag, $handle ) {
	if ( ! in_array( $handle, smm_vite_module_handle(), true ) ) {
		return $tag;
	}

	// Only the external tag becomes a module; `src` may be in any position but
	// must be its own attribute, not the end of `data-src`.
	return preg_replace_callback(
		'/<script\s(?:[^>]*\s)?src=[^>]*>/',
		static function ( $matches ) {
			$script = preg_replace( '/\stype=([\'"])[^\'"]*\1/', '', $matches[0] );

			return str_replace( '<script ', '<script type="module" ', $script );
		},
		$tag
	);
}
add_filter( 'script_loader_tag', 'smm_vite_module_tag', 10, 2 );
