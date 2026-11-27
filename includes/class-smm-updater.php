<?php
/**
 * Updates from GitHub Releases instead of wordpress.org.
 *
 * The plugin header sets `Update URI: https://github.com/DIG-ID/simple-maintenance-mode`,
 * so WordPress (5.8+) asks the `update_plugins_github.com` filter, and never
 * wordpress.org, whether there is a new version. The answer comes from the
 * latest GitHub Release of the public repository, cached for 12 hours.
 *
 * Every download of a release zip is checked against the SHA-256 published
 * next to it (`simple-maintenance-mode.zip.sha256`) before WordPress unpacks
 * it. A missing or different checksum aborts the update.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * GitHub Releases updater with checksum verification.
 *
 * @since 1.1.0
 */
class Smm_Updater {

	/**
	 * GitHub API endpoint for the latest release.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	const API_URL = 'https://api.github.com/repos/DIG-ID/simple-maintenance-mode/releases/latest';

	/**
	 * Only packages under this URL are downloaded and verified by the plugin.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	const DOWNLOAD_PREFIX = 'https://github.com/DIG-ID/simple-maintenance-mode/releases/download/';

	/**
	 * File name of the release zip asset.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	const ZIP_NAME = 'simple-maintenance-mode.zip';

	/**
	 * Site transient caching the latest release.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	const TRANSIENT = 'smm_latest_release';

	/**
	 * Cache lifetime of a successful lookup: 12 hours.
	 *
	 * @since 1.1.0
	 * @var int
	 */
	const CACHE_TTL = 43200;

	/**
	 * Cache lifetime of a failed lookup: 1 hour, so a GitHub outage or rate
	 * limit (60 anonymous requests per hour per IP, shared on some hosts) is
	 * not retried on every admin page.
	 *
	 * @since 1.1.0
	 * @var int
	 */
	const ERROR_TTL = 3600;

	/**
	 * Registers the update hooks.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function register() {
		add_filter( 'update_plugins_github.com', array( $this, 'check_update' ), 10, 3 );
		add_filter( 'upgrader_pre_download', array( $this, 'verify_download' ), 10, 2 );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 10, 3 );
	}

	/**
	 * Answers WordPress's update check for this plugin.
	 *
	 * WordPress compares the returned version with the installed one and lists
	 * the update only when it is newer.
	 *
	 * @since 1.1.0
	 *
	 * @param array|false $update      Update data from earlier callbacks, or false.
	 * @param array       $plugin_data Plugin headers.
	 * @param string      $plugin_file Plugin file relative to the plugins directory.
	 * @return array|false
	 */
	public function check_update( $update, $plugin_data, $plugin_file ) {
		if ( plugin_basename( SMM_FILE ) !== $plugin_file ) {
			return $update;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $update;
		}

		return array(
			'slug'         => 'simple-maintenance-mode',
			'version'      => $release['version'],
			'url'          => $release['url'],
			'package'      => $release['package'],
			'requires'     => '5.8',
			'requires_php' => '7.4',
		);
	}

	/**
	 * Downloads a release zip and checks its SHA-256 before WordPress installs it.
	 *
	 * Runs for any upgrader download (update or install) whose package is a
	 * release asset of this repository; other packages are left to WordPress.
	 * Fails closed: if the checksum cannot be fetched or does not match, the
	 * file is deleted and the update stops with an error.
	 *
	 * @since 1.1.0
	 *
	 * @param false|string|WP_Error $reply   False to let WordPress download, or a result from an earlier filter.
	 * @param string                $package Package URL.
	 * @return false|string|WP_Error Path of the verified file, an error, or $reply untouched.
	 */
	public function verify_download( $reply, $package ) {
		if ( false !== $reply || ! $this->is_release_package( $package ) ) {
			return $reply;
		}

		$expected = $this->fetch_checksum( $package . '.sha256' );
		if ( is_wp_error( $expected ) ) {
			return $expected;
		}

		$file = wp_tempnam( self::ZIP_NAME );
		if ( ! $file ) {
			return new WP_Error( 'smm_download_failed', __( 'Could not create a temporary file for the update.', 'simple-maintenance-mode' ) );
		}

		$response = wp_safe_remote_get(
			$package,
			array(
				'timeout'    => 300,
				'stream'     => true,
				'filename'   => $file,
				'user-agent' => $this->user_agent(),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			wp_delete_file( $file );
			return new WP_Error( 'smm_download_failed', __( 'Could not download the Simple Maintenance Mode update.', 'simple-maintenance-mode' ) );
		}

		$actual = hash_file( 'sha256', $file );
		if ( ! is_string( $actual ) || ! hash_equals( $expected, $actual ) ) {
			wp_delete_file( $file );
			return new WP_Error( 'smm_checksum_mismatch', __( 'The Simple Maintenance Mode update was not installed: its SHA-256 checksum does not match the one published with the release.', 'simple-maintenance-mode' ) );
		}

		return $file;
	}

	/**
	 * Provides the "View details" information for the update screen.
	 *
	 * @since 1.1.0
	 *
	 * @param false|object|array $result Result from earlier callbacks.
	 * @param string             $action API action.
	 * @param object             $args   Request arguments.
	 * @return false|object|array
	 */
	public function plugin_information( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'simple-maintenance-mode' !== $args->slug ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => 'Simple Maintenance Mode',
			'slug'          => 'simple-maintenance-mode',
			'version'       => $release['version'],
			'author'        => '<a href="https://dig.id/">dig.id</a>',
			'homepage'      => 'https://github.com/DIG-ID/simple-maintenance-mode',
			'requires'      => '5.8',
			'requires_php'  => '7.4',
			'last_updated'  => $release['published'],
			'download_link' => $release['package'],
			'sections'      => array(
				'changelog' => '<pre>' . esc_html( $release['notes'] ) . '</pre>'
					. '<p><a href="' . esc_url( $release['url'] ) . '">' . esc_html__( 'Release on GitHub', 'simple-maintenance-mode' ) . '</a></p>',
			),
		);
	}

	/**
	 * Latest release with both assets (zip and checksum), cached.
	 *
	 * @since 1.1.0
	 * @return array|null Keys: version, url, package, published, notes. Null when unavailable.
	 */
	public function get_latest_release() {
		$cached = get_site_transient( self::TRANSIENT );
		if ( is_array( $cached ) ) {
			return empty( $cached['version'] ) ? null : $cached;
		}

		$release = $this->fetch_latest_release();
		set_site_transient( self::TRANSIENT, $release ? $release : array( 'version' => '' ), $release ? self::CACHE_TTL : self::ERROR_TTL );

		return $release;
	}

	/**
	 * Asks the GitHub API for the latest release.
	 *
	 * Only a release with a SemVer tag and both the zip and its .sha256 asset
	 * counts: without the checksum the update could not be verified.
	 *
	 * @since 1.1.0
	 * @return array|null
	 */
	private function fetch_latest_release() {
		$response = wp_safe_remote_get(
			self::API_URL,
			array(
				'timeout'    => 10,
				'user-agent' => $this->user_agent(),
				'headers'    => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) || empty( $data['tag_name'] ) || ! preg_match( '/^v?(\d+\.\d+\.\d+)$/', $data['tag_name'], $match ) ) {
			return null;
		}

		$package  = '';
		$checksum = false;
		foreach ( (array) ( $data['assets'] ?? array() ) as $asset ) {
			$name = $asset['name'] ?? '';
			if ( self::ZIP_NAME === $name ) {
				$package = (string) ( $asset['browser_download_url'] ?? '' );
			} elseif ( self::ZIP_NAME . '.sha256' === $name ) {
				$checksum = true;
			}
		}

		if ( ! $checksum || ! $this->is_release_package( $package ) ) {
			return null;
		}

		return array(
			'version'   => $match[1],
			'url'       => esc_url_raw( (string) ( $data['html_url'] ?? 'https://github.com/DIG-ID/simple-maintenance-mode/releases' ) ),
			'package'   => $package,
			'published' => sanitize_text_field( (string) ( $data['published_at'] ?? '' ) ),
			'notes'     => sanitize_textarea_field( (string) ( $data['body'] ?? '' ) ),
		);
	}

	/**
	 * Downloads a `.sha256` file and returns the hash it contains.
	 *
	 * Accepts the `sha256sum` format ("<hash>  <file name>") or the bare hash.
	 *
	 * @since 1.1.0
	 *
	 * @param string $url Checksum URL.
	 * @return string|WP_Error Lower-case hex hash, or an error.
	 */
	private function fetch_checksum( $url ) {
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'    => 30,
				'user-agent' => $this->user_agent(),
			)
		);

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response )
			&& preg_match( '/^\s*([a-f0-9]{64})\b/i', wp_remote_retrieve_body( $response ), $match ) ) {
			return strtolower( $match[1] );
		}

		return new WP_Error( 'smm_checksum_unavailable', __( 'The Simple Maintenance Mode update was not installed: its SHA-256 checksum could not be downloaded.', 'simple-maintenance-mode' ) );
	}

	/**
	 * Whether a URL is the zip asset of a release of this repository.
	 *
	 * @since 1.1.0
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	private function is_release_package( $url ) {
		return is_string( $url )
			&& 0 === strpos( $url, self::DOWNLOAD_PREFIX )
			&& '/' . self::ZIP_NAME === substr( $url, -strlen( '/' . self::ZIP_NAME ) );
	}

	/**
	 * User agent for requests to GitHub.
	 *
	 * GitHub requires one; this one names the plugin instead of WordPress's
	 * default, which would send the client site's URL.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	private function user_agent() {
		return 'simple-maintenance-mode/' . SMM_VERSION;
	}
}
