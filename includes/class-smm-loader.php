<?php
/**
 * Collects the plugin's actions and filters and registers them with WordPress.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Hook registry.
 *
 * @since 1.0.0
 */
class Smm_Loader {

	/**
	 * Actions to register.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Filters to register.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Queues an action.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          Action name.
	 * @param object $component     Object the callback belongs to.
	 * @param string $callback      Method name on $component.
	 * @param int    $priority      Optional. Priority. Default 10.
	 * @param int    $accepted_args Optional. Number of arguments passed to the callback. Default 1.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Queues a filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          Filter name.
	 * @param object $component     Object the callback belongs to.
	 * @param string $callback      Method name on $component.
	 * @param int    $priority      Optional. Priority. Default 10.
	 * @param int    $accepted_args Optional. Number of arguments passed to the callback. Default 1.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Adds a hook to a collection.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $hooks         Collection (actions or filters).
	 * @param string $hook          Hook name.
	 * @param object $component     Object the callback belongs to.
	 * @param string $callback      Method name on $component.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of arguments passed to the callback.
	 * @return array The updated collection.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Registers the queued filters and actions with WordPress.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}
}
