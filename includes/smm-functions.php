<?php
/**
 * Public PHP API: state of the maintenance mode and content helpers.
 *
 * These functions are the supported way for themes, the dig.id Control Center
 * agent and WP-CLI to read or change the maintenance state. The state itself
 * lives in the `smm_enabled` option (1 on / 0 off), which is part of the public
 * contract and is also read directly by the Control Center agent.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Whether maintenance mode is on.
 *
 * @since 1.1.0
 * @return bool True when visitors get the maintenance page.
 */
function smm_is_enabled() {
	$enabled = 1 === (int) get_option( 'smm_enabled', 0 );

	/**
	 * Filters whether maintenance mode is on.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $enabled Value of the `smm_enabled` option.
	 */
	return (bool) apply_filters( 'smm_enabled', $enabled );
}

/**
 * Turns maintenance mode on.
 *
 * No nonce or capability check: this is meant for trusted code (the Control
 * Center agent, WP-CLI, deploy scripts). Callers handling a user request must
 * check permissions themselves. Fires `smm_toggled` when the state changes.
 *
 * @since 1.1.0
 * @return bool True if the state changed, false if it was already on.
 */
function smm_enable() {
	return smm_set_enabled( true );
}

/**
 * Turns maintenance mode off.
 *
 * Same trust model as smm_enable(). Fires `smm_toggled` when the state changes.
 *
 * @since 1.1.0
 * @return bool True if the state changed, false if it was already off.
 */
function smm_disable() {
	return smm_set_enabled( false );
}

/**
 * Stores the maintenance state.
 *
 * The option is not autoloaded on purpose: it is read once per request and
 * keeping it out of alloptions avoids stale values in persistent object caches.
 *
 * @since 1.1.0
 * @access private
 *
 * @param bool $enabled New state.
 * @return bool True if the stored state changed.
 */
function smm_set_enabled( $enabled ) {
	$current = 1 === (int) get_option( 'smm_enabled', 0 );
	if ( $current === (bool) $enabled ) {
		return false;
	}
	return update_option( 'smm_enabled', $enabled ? 1 : 0, false );
}

/**
 * Fires `smm_toggled` whenever the stored state really changes.
 *
 * Hooked to the option hooks rather than to the toggle handlers, so every path
 * that changes the state (admin bar, Customizer, the API above, WP-CLI,
 * deactivation) is reported once and in the same way.
 *
 * @since 1.1.0
 * @access private
 *
 * @param mixed $old_value Previous option value (false when the option did not exist).
 * @param mixed $value     New option value.
 * @return void
 */
function smm_maybe_fire_toggled( $old_value, $value ) {
	$was = 1 === (int) $old_value;
	$now = 1 === (int) $value;
	if ( $was === $now ) {
		return;
	}

	/**
	 * Fires after maintenance mode was turned on or off.
	 *
	 * @since 1.1.0
	 *
	 * @param bool $enabled New state.
	 * @param int  $user_id ID of the user who changed it; 0 for WP-CLI without
	 *                      --user, the Control Center agent or other system code.
	 */
	do_action( 'smm_toggled', $now, get_current_user_id() );
}
add_action(
	'update_option_smm_enabled',
	'smm_maybe_fire_toggled',
	10,
	2
);
add_action(
	'add_option_smm_enabled',
	static function ( $option, $value ) {
		smm_maybe_fire_toggled( 0, $value );
	},
	10,
	2
);

/**
 * Content for the maintenance page (wrapper for Smm_Utils::get_content()).
 *
 * @since 1.0.0
 *
 * @param string $key      Content key, e.g. 'title', 'address'.
 * @param string $fallback Value used when the Customizer field is empty.
 * @return string
 */
function smm_get_content( $key, $fallback = '' ) {
	return Smm_Utils::get_content( $key, $fallback );
}
