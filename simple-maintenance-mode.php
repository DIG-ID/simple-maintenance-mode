<?php
/**
 * Plugin Name:       Simple Maintenance Mode
 * Description:       Ativa/desativa modo manutenção com um clique na admin bar. Template personalizável, bypass para administradores e editores.
 * Version:           2.0.0
 * License:           GPL v2 or later
 * Text Domain:       simple-maintenance-mode
 */

defined('ABSPATH') || exit;

// ------------------------------------------------------------
// 1. CONSTANTES DO PLUGIN
// ------------------------------------------------------------
const SMM_OPTION_ENABLED = 'smm_enabled';
const SMM_NONCE_ACTION   = 'smm_toggle_maintenance';
const SMM_MENU_ID        = 'smm-maintenance';

define('SMM_FILE', __FILE__);
define('SMM_DIR',  plugin_dir_path(__FILE__));
define('SMM_URL',  plugin_dir_url(__FILE__));

// ------------------------------------------------------------
// 2. INICIALIZAÇÃO DA OPÇÃO (autoload = false)
// ------------------------------------------------------------
add_action('init', function () {
	if (get_option(SMM_OPTION_ENABLED, null) === null) {
		add_option(SMM_OPTION_ENABLED, 0, '', false);
	}
}, 1);

// ------------------------------------------------------------
// 3. FUNÇÕES AUXILIARES (estado, bypass, exceções)
// ------------------------------------------------------------

/**
 * Verifica se o modo manutenção está ativo.
 */
function smm_enabled(): bool {
	return (bool) apply_filters('smm_enabled', (int) get_option(SMM_OPTION_ENABLED, 0) === 1);
}

/**
 * Utilizadores com capacidade de bypass (admin e editor).
 */
function smm_user_can_bypass(): bool {
	if (!is_user_logged_in()) {
		return false;
	}
	$can = current_user_can('manage_options') || current_user_can('edit_others_pages');
	return (bool) apply_filters('smm_user_can_bypass', $can);
}

/**
 * Pedidos que nunca são bloqueados (login, cron, admin, etc.).
 */
function smm_should_allow_request(): bool {
	// Área de administração
	if (is_admin()) {
		return true;
	}

	$script = $_SERVER['SCRIPT_NAME'] ?? '';
	$uri    = $_SERVER['REQUEST_URI'] ?? '';

	// Ficheiros críticos do core
	if (strpos($script, 'wp-login.php') !== false) return true;
	if (strpos($script, 'wp-cron.php')  !== false) return true;

	// REST API – só com bypass ou permissão explícita
	if (defined('REST_REQUEST') && REST_REQUEST) {
		$allow = (bool) apply_filters('smm_allow_rest_request', false, $uri);
		return smm_user_can_bypass() || $allow;
	}

	// AJAX – só com bypass ou permissão explícita
	if (defined('DOING_AJAX') && DOING_AJAX) {
		$action = $_REQUEST['action'] ?? '';
		$allow  = (bool) apply_filters('smm_allow_ajax_action', false, $action);
		return smm_user_can_bypass() || $allow;
	}

	// Filtro genérico para outros casos (webhooks, healthchecks)
	if (apply_filters('smm_allow_request', false, $uri)) {
		return true;
	}

	return false;
}

/**
 * URL para ativar/desativar (com nonce).
 */
function smm_toggle_url(): string {
	return wp_nonce_url(
		admin_url('admin-post.php?action=smm_toggle'),
		SMM_NONCE_ACTION
	);
}

// ------------------------------------------------------------
// 4. HANDLER DO TOGGLE (admin-post)
// ------------------------------------------------------------
add_action('admin_post_smm_toggle', function () {
	if (!is_user_logged_in() || !smm_user_can_bypass()) {
		wp_die('Unauthorized.', 403);
	}
	check_admin_referer(SMM_NONCE_ACTION);

	update_option(SMM_OPTION_ENABLED, smm_enabled() ? 0 : 1, false);

	wp_safe_redirect(wp_get_referer() ?: admin_url());
	exit;
});

// ------------------------------------------------------------
// 5. ADMIN BAR: ITEM E ESTILOS
// ------------------------------------------------------------
add_action('admin_bar_menu', function ($wp_admin_bar) {
	if (!is_admin_bar_showing()) return;
	if (!is_user_logged_in() || !smm_user_can_bypass()) return;

	$enabled = smm_enabled();

	$title = $enabled
		? '<span class="ab-icon dashicons dashicons-hammer"></span><span class="ab-label">Maintenance ON</span>'
		: '<span class="ab-icon dashicons dashicons-hammer"></span><span class="ab-label">Maintenance OFF</span>';

	$wp_admin_bar->add_node([
		'id'    => SMM_MENU_ID,
		'title' => $title,
		'href'  => smm_toggle_url(),
		'meta'  => [
			'class' => $enabled ? 'smm-on' : 'smm-off',
			'title' => $enabled ? 'Desativar modo manutenção' : 'Ativar modo manutenção',
		],
	]);
}, 100);

// Estilos inline para o admin bar
add_action('admin_head', 'smm_adminbar_styles');
add_action('wp_head', 'smm_adminbar_styles');
function smm_adminbar_styles() {
	if (!is_admin_bar_showing()) return;
	if (!is_user_logged_in() || !smm_user_can_bypass()) return;
	?>
	<style>
		#wpadminbar #wp-admin-bar-<?php echo esc_attr(SMM_MENU_ID); ?>.smm-on > .ab-item {
			background: #e19f62 !important;
			color: #fff !important;
		}
		#wpadminbar #wp-admin-bar-<?php echo esc_attr(SMM_MENU_ID); ?>.smm-off > .ab-item {
			background: #797e7d !important;
			color: #fff !important;
		}
		#wpadminbar #wp-admin-bar-<?php echo esc_attr(SMM_MENU_ID); ?> .ab-icon {
			margin-right: 4px;
		}
	</style>
	<?php
}

// ------------------------------------------------------------
// 6. ADMIN NOTICE (quando ativo)
// ------------------------------------------------------------
add_action('admin_notices', function () {
	if (!smm_enabled()) return;
	if (!is_user_logged_in() || !smm_user_can_bypass()) return;

	$link = esc_url(smm_toggle_url());
	echo '<div class="notice notice-error">';
	echo '<p><strong>Modo manutenção ATIVO.</strong> Visitantes estão a ver a página de manutenção. ';
	echo '<a href="' . $link . '">Desativar agora</a></p>';
	echo '</div>';
});

// ------------------------------------------------------------
// 7. FUNÇÕES DE CONTEÚDO (para mensagens personalizáveis)
// ------------------------------------------------------------
/**
 * Obtém conteúdo personalizado (ACF ou filtro).
 */
function smm_get_content(string $key, string $default = ''): string {
	// Se o ACF estiver ativo, lê do campo de opções.
	if (function_exists('get_field')) {
		$value = get_field('smm_' . $key, 'option');
		if (is_string($value) && $value !== '') {
			return $value;
		}
	}
	// Caso contrário, usa filtro (permite que temas/plugins personalizem).
	return (string) apply_filters('smm_content_' . $key, $default);
}

// ------------------------------------------------------------
// 8. SELEÇÃO DO TEMPLATE DE MANUTENÇÃO (override no tema)
// ------------------------------------------------------------
/**
 * Retorna o caminho absoluto do template a ser usado.
 * Ordem de prioridade:
 * 1. Tema ativo: /simple-maintenance-mode/maintenance.php
 * 2. Tema filho (se existir) – locate_template já trata isso
 * 3. Template padrão do plugin: /templates/maintenance.php
 */
function smm_get_maintenance_template(): string {
	$template = locate_template('simple-maintenance-mode/maintenance.php');
	if (!empty($template)) {
		return $template;
	}
	return SMM_DIR . 'templates/maintenance.php';
}

// ------------------------------------------------------------
// 9. CARREGAMENTO DOS ASSETS VITE (PARA O TEMPLATE)
// ------------------------------------------------------------
/**
 * Enqueue styles and scripts
 */
function ghe_theme_enqueue_styles() {

	//Get the theme data
	$the_theme     = wp_get_theme();
	$theme_version = $the_theme->get( 'Version' );

	// Register Theme main style.
	wp_register_style( 'plugin-styles', plugin_dir_url( __FILE__ ) . '/dist/css/main.css', array(), $theme_version );
	// Enqueue theme stylesheet.
	wp_enqueue_style( 'plugin-styles' );

	//wp_enqueue_script( 'jquery', false, array(), $theme_version, true );
	//wp_enqueue_script( 'plugin-scripts', get_stylesheet_directory_uri() . '/dist/js/main.js', array( 'jquery' ), $theme_version, true );
}

add_action( 'wp_enqueue_scripts', 'ghe_theme_enqueue_styles' );

// ------------------------------------------------------------
// 10. GATE PRINCIPAL (template_redirect)
// ------------------------------------------------------------
add_action('template_redirect', function () {
	// --------------------------------------------------------
	// A) Verificar se devemos mostrar o template
	// --------------------------------------------------------
	$is_preview = isset($_GET['smm_preview']) && $_GET['smm_preview'] === '1' && current_user_can('manage_options');
	$is_active  = smm_enabled();

	if (!$is_active && !$is_preview) {
		return;
	}

	if ($is_active && !$is_preview) {
		// Modo normal: administradores/EDIT bypass, exceções bypass
		if (smm_user_can_bypass()) return;
		if (smm_should_allow_request()) return;
	}

	// Se chegou aqui, vamos mostrar a página de manutenção (ou preview)
	// --------------------------------------------------------
	// B) Preparar cabeçalhos HTTP
	// --------------------------------------------------------
	if (!defined('DONOTCACHEPAGE'))   define('DONOTCACHEPAGE', true);
	if (!defined('DONOTCACHEOBJECT')) define('DONOTCACHEOBJECT', true);
	if (!defined('DONOTMINIFY'))      define('DONOTMINIFY', true);

	status_header(503);
	header('Retry-After: 600');
	header('X-Robots-Tag: noindex, nofollow', true);
	nocache_headers();

	// --------------------------------------------------------
	// C) Incluir o template
	// --------------------------------------------------------
	$template = smm_get_maintenance_template();
	$template = apply_filters('smm_maintenance_template', $template);

	if (!is_readable($template)) {
		wp_die('Maintenance template not found.', 'Maintenance', ['response' => 503]);
	}

	// Disponibiliza algumas variáveis úteis para o template
	$smm_vars = [
		'site_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
		'home_url'  => home_url('/'),
		'login_url' => wp_login_url(),
		'is_preview'=> $is_preview,
	];
	set_query_var('smm_vars', $smm_vars); // disponível como $smm_vars no template

	include $template;
	exit;
}, 0);