<?php

function profile_url_init(){
  elgg_register_plugin_hook_handler('forward', '404', 'profile_url_router', 0);
  elgg_register_plugin_hook_handler('registeruser:validate:username', 'all', 'profile_url_check_username');
  elgg_register_entity_url_handler('user', 'all', 'profile_url_member_url');
}

/**
 * 	Hook on 404 forwards
 *  if first part is a valid username, we'll send them to that profile
 */
function profile_url_router($hook, $type, $return, $params){
  
  $base_path = parse_url(elgg_get_site_url(), PHP_URL_PATH);
  $current_path = parse_url($params['current_url'], PHP_URL_PATH);
  if ($base_path != '/') {
    $current_path = str_replace($base_path, '', $current_path);
  } else {
    $current_path = substr($current_path, 1);
  }
  $parts = explode('/', $current_path);
  
  if (count($parts) == 1) {
    $username = urldecode($parts[0]);
    $user = get_user_by_username($username);
  
    if ($user) {
      if (profile_page_handler(array($username))) {
        exit;
      }
    }
  }

  return $return;
}

// create short urls for user profiles
function profile_url_member_url($user){
  return elgg_get_site_url() . urlencode($user->username);
}

// prevent new users from taking usernames == pagehandlers
function profile_url_check_username($hook, $type, $return, $params) {
  global $CONFIG;
  
  $handlers = array_keys($CONFIG->pagehandler);
  return !in_array($params['username'], $handlers);
}

elgg_register_event_handler('init', 'system', 'profile_url_init');