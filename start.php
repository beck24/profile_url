<?php

namespace ProfileURL;

const PLUGIN_ID = 'profile_url';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

function init() {
	elgg_register_plugin_hook_handler('forward', '404', __NAMESPACE__ . '\\forward_hook', 0);
	elgg_register_plugin_hook_handler('registeruser:validate:username', 'all', __NAMESPACE__ . '\\check_username');
	elgg_register_plugin_hook_handler('entity:url', 'user', __NAMESPACE__ . '\\member_url');
}

function forward_hook($hook, $type, $return, $params) {

	$base_path = parse_url(elgg_get_site_url(), PHP_URL_PATH);
	$current_path = parse_url($params['current_url'], PHP_URL_PATH);
	$current_path = ($base_path == '/') ? substr($current_path, 1) : str_replace($base_path, '', $current_path);
	$parts = explode('/', $current_path);

	if (count($parts) == 1 && $user = get_user_by_username(str_replace('-', '.', $parts[0]))) {
		elgg_set_context('profile');
		$result = elgg_trigger_plugin_hook('route', 'profile', null, array(
			'identifier' => 'profile',
			'handler' => 'profile',
			'segments' => array($user->username)
		));

		if ($result === false) {
			// it's been handled
			exit;
		}

		// yes private API but there's nothing we can do about it
		$handlers = _elgg_services()->router->getPageHandlers();
		if (is_callable($handlers['profile'])) {
			$result = call_user_func($handlers['profile'], array($user->username), 'profile');

			if ($result) {
				exit;
			}
		}
	}

	return $return;
}

function member_url($h, $t, $r, $p) {
	return elgg_get_site_url() . str_replace('.', '-', $p['entity']->username);
}

function check_username($h, $t, $r, $p) {
	// yes private API but there's nothing we can do about it
	$handlers = _elgg_services()->router->getPageHandlers();
	return !in_array($p['username'], array_keys($handlers));
}
