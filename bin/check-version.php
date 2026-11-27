<?php
/**
 * Checks that every version number in the project agrees.
 *
 * The version lives in the plugin header, the SMM_VERSION constant, the
 * "Stable tag" of README.txt, package.json and the latest released entry of
 * CHANGELOG.md. With --tag=vX.Y.Z it also checks them against the release tag.
 *
 * Usage: php bin/check-version.php [--tag=vX.Y.Z]
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.1.0
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

/**
 * Collects the version numbers and compares them.
 *
 * @since 1.1.0
 *
 * @param string      $root Project root directory.
 * @param string|null $tag  Release tag to compare against, e.g. "v1.1.0".
 * @return int Process exit code: 0 when all versions agree, 1 otherwise.
 */
function smm_check_version( $root, $tag ) {
	$sources = array(
		'plugin header' => array( 'simple-maintenance-mode.php', '/^\s*\*\s*Version:\s*(\S+)/m' ),
		'SMM_VERSION'   => array( 'simple-maintenance-mode.php', "/define\(\s*'SMM_VERSION',\s*'([^']+)'/" ),
		'README.txt'    => array( 'README.txt', '/^Stable tag:\s*(\S+)/m' ),
		'package.json'  => array( 'package.json', '/^\s*"version":\s*"([^"]+)"/m' ),
		'CHANGELOG.md'  => array( 'CHANGELOG.md', '/^## \[(\d+\.\d+\.\d+)\]/m' ),
	);

	$found = array();

	foreach ( $sources as $label => $source ) {
		$contents        = (string) file_get_contents( $root . '/' . $source[0] );
		$found[ $label ] = 1 === preg_match( $source[1], $contents, $match ) ? $match[1] : '(missing)';
	}

	if ( null !== $tag ) {
		$found['tag'] = ltrim( $tag, 'v' );
	}

	if ( 1 === count( array_unique( $found ) ) && 1 === preg_match( '/^\d+\.\d+\.\d+$/', reset( $found ) ) ) {
		echo 'Version ' . reset( $found ) . " is consistent.\n";
		return 0;
	}

	fwrite( STDERR, "Version mismatch:\n" );

	foreach ( $found as $label => $version ) {
		fwrite( STDERR, sprintf( "  %-14s %s\n", $label, $version ) );
	}

	return 1;
}

$smm_tag = null;

foreach ( array_slice( $argv, 1 ) as $smm_arg ) {
	if ( 0 === strpos( $smm_arg, '--tag=' ) ) {
		$smm_tag = substr( $smm_arg, 6 );
	}
}

exit( smm_check_version( dirname( __DIR__ ), $smm_tag ) );
