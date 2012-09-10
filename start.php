<?php

function profile_url_init() {
  elgg_register_plugin_hook_handler('forward', '404', 'profile_url_router', 0);
  elgg_register_plugin_hook_handler('registeruser:validate:username', 'all', 'profile_url_check_username');
  elgg_register_entity_url_handler('user', 'all', 'profile_url_member_url');
}

function profile_url_router($hook, $type, $return, $params) {
  
  $base_path = parse_url(elgg_get_site_url(), PHP_URL_PATH);
  $current_path = parse_url($params['current_url'], PHP_URL_PATH);
  $current_path = ($base_path == '/') ? substr($current_path,1) : str_replace($base_path, '', $current_path);
  $parts = explode('/', $current_path);
  
  if (count($parts) == 1 && $user = get_user_by_username(str_replace('-', '.', $parts[0]))) {
    elgg_set_context('profile');
    if (profile_page_handler(array($user->username))) {
      exit;
    }
  }

  return $return;
}

function profile_url_member_url($user) {
  return elgg_get_site_url() . str_replace('.', '-', $user->username);
}

function profile_url_check_username($hook, $type, $return, $params) {
  global $CONFIG;
  return !in_array($params['username'], array_keys($CONFIG->pagehandler));
}

elgg_register_event_handler('init', 'system', 'profile_url_init');