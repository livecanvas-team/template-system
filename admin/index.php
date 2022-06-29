<?php

new class {

  public $name = 'tangible_template_system';

  // Remember to update the version - Expected format: YYYYMMDD
  public $version = '20220628';
  public $url;

  function __construct() {

    $name     = $this->name;
    $priority = 99999999 - absint( $this->version );

    add_action( $name, [ $this, 'load' ], $priority );

    add_action('plugins_loaded', function() use ( $name ) {
      if ( ! did_action( $name )) do_action( $name );
    }, 0);

    $this->path      = __DIR__;
    $this->file_path = __FILE__;
    $this->url = plugins_url( '/', realpath( __FILE__ ) );
  }

  // Dynamic methods
  function __call( $method = '', $args = [] ) {
    if ( isset( $this->$method ) ) {
      return call_user_func_array( $this->$method, $args );
    }
    $caller = current( debug_backtrace() );
    echo "Warning: Undefined method \"$method\" for {$this->name}, called from <b>{$caller['file']}</b> in <b>{$caller['line']}</b><br>";
  }

  function load() {

    $name   = $this->name;
    $plugin = $system = $this;

    remove_all_filters( $name ); // First one to load wins
    tangible_template_system( $this );

    /**
     * Currently consolidating all features to be internal to the template system,
     * removing dependecy on plugin framework and external modules.
     */

    require_once __DIR__ . '/../interface/index.php';
    require_once __DIR__ . '/../loop/index.php';
    require_once __DIR__ . '/../logic/index.php';
    require_once __DIR__ . '/../template/index.php';

    require_once __DIR__ . '/../tester/index.php';

    // Wait for latest version of plugin framework
    add_action('plugins_loaded', function() use ( $plugin ) {

      $framework = tangible();

      $loop      = $plugin->loop = tangible_loop();
      $logic     = $plugin->logic = tangible_logic();
      $interface = $plugin->interface = tangible_interface();
      $html      = $plugin->html = tangible_template();
      $ajax      = $plugin->ajax = $framework->ajax();

      /**
       * Template post types and fields, editor, management
       */

      require_once __DIR__ . '/post-types/index.php';

      require_once __DIR__ . '/data.php';
      require_once __DIR__ . '/editor/index.php';
      require_once __DIR__ . '/fields.php';
      require_once __DIR__ . '/save.php';
      require_once __DIR__ . '/render/index.php';
      require_once __DIR__ . '/tag.php';

      require_once __DIR__ . '/template-assets/index.php';
      require_once __DIR__ . '/location/index.php';

      require_once __DIR__ . '/universal-id/index.php';
      require_once __DIR__ . '/import-export/index.php';

      require_once __DIR__ . '/../features/index.php';
      require_once __DIR__ . '/integrations/index.php';

      // TODO: Convert to use Cloud Client module
      // require_once __DIR__.'/cloud/index.php';

      do_action( "{$this->name}_ready", $plugin );

    }, 8); // Before plugins register

    add_action('plugins_loaded', function() use ( $plugin ) {

      // For any callbacks that registered later
      do_action( "{$plugin->name}_ready", $plugin );

    }, 12); // After plugins register
  }

  function ready( $callback ) {
    if ( did_action( "{$this->name}_ready" ) ) {
      return $callback( $this );
    }
    add_action( "{$this->name}_ready", $callback );
  }

  function run_tests() {
    include __DIR__ . '/../tests/index.php';
  }

  /**
   * Mock $plugin methods during transition from plugin to module
   */
  function is_multisite() {
    return false;
  }
  function get_settings() {
    return [];
  }
  function update_settings() {}
};

if ( ! function_exists( 'tangible_template_system' ) ) :

  function tangible_template_system( $arg = false ) {
    static $o;
    return $arg === false ? $o : ( $o = $arg );
  }

endif;
