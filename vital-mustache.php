<?php
/*
    Plugin Name: Vital Mustache
    Plugin URI:
    Description: Adds support for Mustache templates to Wordpress
    Version: 1.0
    Author: Vital
    Author URI: http://vtldesign.com
    Text Domain: vitaldesign
    License: GPLv2

    Copyright 2017  VITAL DESIGN  (email : developer@vtldesign.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit if accessed directly
if (! defined('ABSPATH')) exit;

class VitalMustache {
    private $plugin_path;
    private $plugin_url;

    public static $engine;

    /**
     * Initialize plugin
     */
    public function __construct() {
        $this->plugin_path    = plugin_dir_path(__FILE__);
        $this->plugin_url     = plugin_dir_url(__FILE__);

        register_activation_hook( __FILE__, array('VitalMustache', 'activate'));

        $mustache_path = $this->plugin_path . '/lib/mustache/mustache.php';
        if(file_exists($mustache_path)){
          require_once($mustache_path);
          VitalMustache::initialize();
        } else {
          add_action('admin_notices', array('VitalMustache', '__no_mustache'));
        }

    }

    public static function __no_mustache(){
      $class = 'notice notice-error';
      $message = __( 'Mustache lib not found! Try reactivating the plugin.');

      printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }

    public static function __no_template_folder(){
      $opt = get_option('vital_mustache_option');
      $template_folder = $opt['vital_mustache_template_folder'];
      $template_folder_path = sprintf('%s/%s', get_template_directory(), $template_folder);
      $settings_url = admin_url('options-general.php?page=vital_mustache');

      $class = 'notice notice-error';
      $message = __("Could not read the path <strong>{$template_folder_path}</strong>! Check your <a href=\"{$settings_url}\">Mustache Settings</a>.");

      printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }

    public static function __activation_error(){
      $class = 'notice notice-error';
      $message = __( 'Error activating the plugin :(');

      printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }

    private static function initialize(){
      $dir = sprintf('%s/mustache-templates', get_template_directory());
      VitalMustache::$engine = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader($dir),
        'partials_loader' => new Mustache_Loader_FilesystemLoader($dir.'/partials'),
      ));

      require_once(plugin_dir_path(__FILE__) . '/public/functions.php')
    }

    public static function activate(){
      VitalMustache::BuildMustache();
      VitalMustache::BuildTemplateFolders();
    }

    private static function BuildMustache(){
      ob_start();
      $mustache_path = plugin_dir_path(__FILE__) . '/lib/mustache/bin/build_bootstrap.php';
      require_once($mustache_path);
      ob_end_flush();
    }

    private static function BuildTemplateFolders(){
      $template_folder = sprintf('%s/mustache-templates/partials', get_template_directory());
      mkdir($template_folder, 0755, true);
    }
}

new VitalMustache();
