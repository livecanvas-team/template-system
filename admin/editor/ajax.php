<?php
use tangible\ajax;
use tangible\template_system;

// Save template via AJAX

ajax\add_action('tangible_template_editor_save', function( $data = [] ) use ( $plugin ) {

  $result = $plugin->save_template_post( $data );

  if (is_wp_error( $result )) return ajax\error([
    'message' => 'Save failed: ' . ( $result->get_error_message() ),
  ]);

  return [
    'message' => 'Saved',
  ];
});

ajax\add_action('tangible_template_editor_render', function( $data = [] ) use ( $plugin ) {

  if (!template_system\can_user_edit_template()) return ajax\error([
    'message' => 'Not allowed'
  ]);

  /**
   * Set preview state to disable Redirect, etc.
   * @see /integrations/index.php
   */
  $plugin->set_template_preview_state( true );

  /**
   * TODO: Setup default query as preview context 
   */

   $loop = template_system::$loop;
   $html = template_system::$html;

   $loop->current_context = $html->loop_tag([
      'instance' => true,
      'post_type' => 'post',
      'count' => 3,
    ], [] );


  /**
   * TODO: Render styles and scripts for this context
   */

  ob_start();
  template_system\location\enqueue_style_templates();
  $style = ob_get_clean();
  
  ob_start();
  template_system\location\enqueue_script_templates();
  $script = ob_get_clean();

  /** @see /admin/template-post/render */
  $result = $plugin->render_template_post($data);

  return [
    'result' => $style . $result . $script,
  ];
});
