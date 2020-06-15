<?php

require_once 'vendor/pw-updater.php';

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class ACF_Auto_Blocks_Updater extends PW_Updater {

  public $username = 'benplum';
  public $repository = 'ACF-Auto-Blocks';
  public $requires = '5.0';
  public $tested = '5.0.2';

  public function __construct() {
    $this->parent = ACF_Auto_Blocks::get_instance();

    parent::__construct();
  }
}


// Instance

ACF_Auto_Blocks_Updater::get_instance();
