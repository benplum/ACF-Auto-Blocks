<?php

// PW GitHub Updater v 1.1.1

if ( ! class_exists( 'PW_GitHub_Updater' ) ) {

class PW_GitHub_Updater {

  protected static $instances = array();

  public $parent;

  public $plugin;
  public $basename;
  public $active;
  public $response;

  public $requires = '0';
  public $tested = '0';

  protected $slug;

  public static function get_instance() {
    $called_class = get_called_class();

    if ( empty( static::$instances[ $called_class ] ) || ! ( static::$instances[ $called_class ] instanceof $called_class ) ) {
      static::$instances[ $called_class ] = new $called_class();
    }

    return static::$instances[ $called_class ];
  }

  public function __construct() {
    add_action( 'admin_init', array( $this, 'admin_init' ) );

    add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
    add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3);
    add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
  }

  // Set props
  public function admin_init() {
    $this->plugin = get_plugin_data( $this->parent->file );
    $this->basename = plugin_basename( $this->parent->file );
    $this->active = is_plugin_active( $this->basename );
  }

  protected function ensure_initialized() {
    if ( empty( $this->basename ) || empty( $this->plugin ) ) {
      $this->admin_init();
    }
  }

  // Get repo
  public function get_repository() {
    if ( is_null( $this->response ) ) {
      $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/tags', $this->username, $this->repository );

      $response = wp_remote_get(
        $request_uri,
        array(
          'timeout' => 15,
          'headers' => array(
            'Accept' => 'application/vnd.github+json',
          ),
        )
      );

      if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        $this->response = array();

        return;
      }

      $response = json_decode( wp_remote_retrieve_body( $response ), true );

      if ( is_array( $response ) ) {
        $response = current( $response );
      }

      if ( ! is_array( $response ) || empty( $response['name'] ) || empty( $response['zipball_url'] ) ) {
        $response = array();
      }

      $this->response = $response;
    }
  }

  protected function get_slug() {
    $this->ensure_initialized();

    if ( ! isset( $this->slug ) ) {
      $this->slug = strtok( $this->basename, '/' );
    }

    return $this->slug;
  }

  protected function normalize_version( $version ) {
    return preg_replace( '/^v/i', '', (string) $version );
  }

  // Check our
  public function modify_transient( $transient ) {
    $this->ensure_initialized();

    if ( is_object( $transient ) && property_exists( $transient, 'checked' ) ) {
      $this->get_repository();

      if ( empty( $this->response['name'] ) ) {
        return $transient;
      }

      $checked = $transient->checked;

      if ( empty( $checked[ $this->basename ] ) ) {
        return $transient;
      }

      $new_version = $this->normalize_version( $this->response['name'] );
      $current_version = $this->normalize_version( $checked[ $this->basename ] );
      $should_update = version_compare( $new_version, $current_version, 'gt' );

      if ( $should_update ) {
        $package = $this->response['zipball_url'];

        $plugin = array(
          'url' => $this->plugin['PluginURI'],
          'slug' => $this->get_slug(),
          'package' => $package,
          'new_version' => $new_version,
        );

        $transient->response[ $this->basename ] = (object) $plugin;
      }
    }

    return $transient;
  }

  public function plugins_api( $result, $action, $args ) {
    $this->ensure_initialized();

    if ( ! empty( $args->slug ) && $args->slug == $this->get_slug() ) {
      $this->get_repository();

      if ( empty( $this->response['name'] ) || empty( $this->response['zipball_url'] ) ) {
        return $result;
      }

      $version = $this->normalize_version( $this->response['name'] );

      $plugin = array(
        'name' => $this->plugin['Name'],
        'slug' => $this->get_slug(),
        'requires' => $this->requires,
        'tested' => $this->tested,
        'version' => $version,
        'author' => $this->plugin['AuthorName'],
        'author_profile' => $this->plugin['AuthorURI'],
        'last_updated' => '',
        'homepage' => $this->plugin['PluginURI'],
        'short_description' => $this->plugin['Description'],
        'sections' => array(
          'description' => $this->plugin['Description'],
          'changelog' => sprintf( 'Latest GitHub tag: %s', $version ),
        ),
        'download_link' => $this->response['zipball_url']
      );

      return (object) $plugin;
    }

    return $result;
  }

  public function after_install( $response, $hook_extra, $result ) {
    global $wp_filesystem;

    $this->ensure_initialized();

    if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->basename ) {
      return $result;
    }

    if ( ! is_object( $wp_filesystem ) || empty( $result['destination'] ) ) {
      return $result;
    }

    $install_directory = plugin_dir_path( $this->parent->file );

    if ( untrailingslashit( $result['destination'] ) !== untrailingslashit( $install_directory ) ) {
      $wp_filesystem->move( $result['destination'], $install_directory, true );
    }

    $result['destination'] = $install_directory;

    if ( $this->active ) {
      activate_plugin( $this->basename );
    }

    return $result;
  }

}

}
