<?php
namespace Tests\Template\Modules;

class Switch_TestCase extends \WP_UnitTestCase {
  public function test() {

    $error = null;
    set_error_handler(function( $errno, $errstr, ...$args ) use ( &$error ) {
      $error = [ $errno, $errstr, $args ];
      restore_error_handler();
    });

    $html = tangible_template();

    $this->assertEquals( true, isset($html->tags['Switch']) );

    $result = $html->render('<Switch />');

    $this->assertNull( $error );
    // $this->assertEquals( true, !empty($result) );
  }
}
