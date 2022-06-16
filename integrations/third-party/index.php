<?php

/**
 * Third-party plugin integrations
 *
 * Loops & Logic Pro uses this to extend content types, fields, and conditions.
 *
 * It should be documented to allow third-party developers.
 */

$plugin->integration_configs = []; // A list of configs

/**
 * Keep old variable name for backward compatibility
 *
 * Remove when Pro plugin is updated to use better named variable above.
 *
 * @see tangible-blocks-pro/includes/admin/admin-notice.php
 */
$plugin->plugin_integrations = &$plugin->integration_configs;

$plugin->register_plugin_integration = function($config) use ($plugin, $framework) {

  foreach ([
    'slug',
    'title',
    'url',
    'active'
  ] as $key) {

    if (!isset($config[ $key ])) throw new Exception("Property \"$key\" is required");
  }

  static $tangible_plugins;

  if (empty($tangible_plugins)) {
    $tangible_plugins = $framework->get_plugins();
  }

  $config['dependency_active'] = $config['active'];


  $previous_plugin_name = 'tangible-loops-for-' . $config['slug'];

  if (isset( $tangible_plugins[ $previous_plugin_name ] )) {

    /**
     * If add-on plugins replaced by Pro are active, do not load the new integration.
     *
     * This avoids loading the same integration twice. There will be an admin notice to
     * prompt the user to uninstall the add-on(s).
     *
     * @see tangible-loops-and-logic-pro/includes/admnin/admin-notice.php
     */

    $config['previous_plugin'] = $tangible_plugins[ $previous_plugin_name ];
    $config['active'] = false;
  }


  if ( ! $plugin->is_integration_enabled( $config['slug'] ) ) {
    $config['active'] = false;
  }

  $plugin->integration_configs []= $config;

  return $config['active'];
};


/**
 * Each integration can register an object instance
 *
 * @see ../wp-fusion/index.php
 * @see tangible-blocks-pro/includes/integrations/index.php
 */
$plugin->integration_instances = new stdClass; // name => $object

$plugin->set_integration = function($name, $object) use ($plugin) {
  $plugin->integration_instances->$name = $object;
};

$plugin->get_integration = function($name) use ($plugin) {
  if (isset($plugin->integration_instances->$name)) {
    return $plugin->integration_instances->$name;
  }
  return false;
};


/**
 * Get plugin settings for integrations enabled
 * Each checkbox is saved as string 'true' or 'false'
 */

$plugin->get_settings_for_integrations_enabled = function() use ($plugin) {

  static $integrations_enabled;

  if (empty($integrations_enabled)) {

    $settings = $plugin->get_settings();

    $integrations_enabled = isset($settings['integrations_enabled'])
      ? $settings['integrations_enabled']
      : []
    ;
  }

  return $integrations_enabled;
};


/**
 * Check if single integration by name is enabled
 */

$plugin->is_integration_enabled = function($slug) use ($plugin) {

  $integrations_enabled = $plugin->get_settings_for_integrations_enabled();

  return !isset($integrations_enabled[ $slug ]) // Enabled by default
    || $integrations_enabled[ $slug ]==='true'
  ;
};


/**
 * Add submenu item Tangible -> Integrations
 */
add_action(
  $plugin->is_multisite() ? 'network_admin_menu' : 'admin_menu',
  function() use ($framework, $plugin) {

    if (empty($plugin->integration_configs)) return;

    // https://developer.wordpress.org/reference/functions/add_submenu_page/
    add_submenu_page(
      'tangible', // Parent menu slug
      'Integrations', // Page title
      'Integrations', // Menu title
      'manage_options', // Capability
      'integrations', // Menu slug
      require_once __DIR__.'/admin.php', // Callback
      30 // Position
    );

  }
  ,
  99 // At the bottom
);
