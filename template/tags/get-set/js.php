<?php

/**
 * Variable type "js"
 */
$html->register_variable_type('js', [
  'set' => function($name, $atts, $content, &$memory) use ($html) {

    // Ensure valid variable name
    $name = preg_replace("/[^a-zA-Z0-9_]+/i", "", $name);

    $content = $html->render( $content );

    $type = isset($atts['type']) ? $atts['type'] : 'string';
    switch ($type) {
      case 'string':
        // Wrap in quotes
        $content = '"' . str_replace('"', '\"', $content) . '"';
      break;
      // case 'number':
      // case 'map':
      // case 'object':
      // case 'list':
      // case 'array':
      // case 'raw':
      default:
        // No formatting
    }

    $memory[ $name ] = $content;
  },
  'get' => function($name, $atts, &$memory) use ($html) {
    return isset($memory[ $name ]) ? $memory[ $name ] : '';
  },
]);

$html->get_js_variables = function() use ($html) {
  return $html->variable_type_memory['js'];
};

$html->clear_js_variables = function() use ($html) {
  $html->variable_type_memory['js'] = [];
};
