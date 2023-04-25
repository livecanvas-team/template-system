<?php

/**
 * For HTML tags - Render attributes to string
 */
$html->render_attributes = function($atts, $options = []) use ($html) {

  $i = 0;
  $content = '';

  $atts = apply_filters('tangible_template_render_attributes', $atts);

  $render_attribute_value = $html->render_attribute_value;

  if (isset($atts['tag-attributes'])) {

    $value = $render_attribute_value('tag-attributes', $atts['tag-attributes']);
    /**
     * Parse as tag attributes to support key, key=value
     */
    $parsed = $html->parse('<div ' . $value . '></div>');

    if (!empty($parsed) && !empty($parsed[0]['attributes'])) {
      foreach ($parsed[0]['attributes'] as $key => $value) {
        if ($key==='keys') {
          if (!isset($atts['keys'])) $atts['keys'] = [];
          $atts['keys'] = array_merge($value, $atts['keys']);
          continue;
        }
        $atts[ $key ] = $value;
      }
    }

    unset($atts['tag-attributes']);
  }

  if (isset($atts['keys'])) {
    // Attributes without values
    foreach ($atts['keys'] as $key) {
      if ($i > 0) $content .= ' ';
      $content .= $key;
      $i++;
    }
    unset($atts['keys']);
  }

  foreach ($atts as $key => $value) {

    if ($i > 0) $content .= ' ';

    if ($value === '') {
      // Strict equal empty to allow false or 0
      $content .= $key;
    } else {

      $value = $render_attribute_value($key, $value, $options);

      /**
       * Encode <, >, &, ” and ‘ characters. Will not double-encode entities.
       * @see https://developer.wordpress.org/reference/functions/esc_attr/
       */
      $value = esc_attr($value);

      $content .= "$key=\"$value\"";
    }

    $i++;
  }

  return $content;
};

/**
 * For template tags - Render attributes to array
 */
$html->render_attributes_to_array = function($atts, $options = []) use ($html) {

  $render_attribute_value = $html->render_attribute_value;

  foreach ($atts as $key => $value) {
    if ($key === 'keys') continue;
    if ($key === 'tag-attributes') {


  $skip_keys = $options['skip_render_keys'] ?? [];

  foreach ($atts as $key => $value) {
    if ($key === 'keys' || in_array($key, $skip_keys)) continue;
    $atts[ $key ] = $render_attribute_value($key, $value, $options);
  }

  // Always provide property "keys" so dynamic tags can check directly
  if (!isset($atts['keys'])) $atts['keys'] = [];

  return $atts;
};

$html->should_render_attribute = function($key, $value) {

  if (!is_string($value)) return false;

  /**
   * Skip rendering attributes with JSON strings.
   *
   * Notably, the style attribute is rendered to allow dynamic styles. The use of
   * curly braces {} there is fine.
   */

  $c = substr( $value, 0, 1 );

  if ($c === '{' || $c === '[') {
    $c2 = substr( $value, 1, 1 );
    $is_json =
      preg_match('/\s/', $c) // Match any whitespace character, including new line
      || $c2==='"' || $c2==='&'
      || ($c==='[' && ($c2==='{' || $c2==='[' || $c2===']'))
      || ($c==='{' && $c2==='}')
    ;
    return !$is_json;
  }

  return true;
};

$html->render_attribute_value = function($key, $value, $options = []) use ($html) {

  $should_render_attribute = $html->should_render_attribute;
  $render = $html->render;

  if (
    (isset($options['render_attributes']) && ! $options['render_attributes'])
    || ! $should_render_attribute($key, $value)
  ) return $value;

  $pair = ['{', '}'];
  $tag_pair = ['<', '>'];

  if (strpos($value, $pair[0])===false || strpos($value, $pair[1])===false) return $value;

  $value = $render(
    str_replace(['<<', '>>'], $pair, // Double-brackets {{ }} to escape
      str_replace(array_merge($tag_pair, $pair), array_merge(['&lt;', '&gt;'], $tag_pair), $value)
    ),
    $options
  );

  return str_replace($tag_pair, $pair, $value); // Restore unrendered tags
};
