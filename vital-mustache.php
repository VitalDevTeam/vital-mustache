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

    public static $DEFAULT_TEMPLATE_FOLDER = 'mustache-templates';

    private $plugin_path;
    private $plugin_url;

    public static $engine;

    /**
     * Initialize plugin
     */
    public function __construct() {

        $this->plugin_path    = plugin_dir_path(__FILE__);
        $this->plugin_url     = plugin_dir_url(__FILE__);

        require $this->plugin_path . 'admin.php';

        $mustache_path = $this->plugin_path . '/lib/mustache/mustache.php';
        if(file_exists($mustache_path)){
          require_once($mustache_path);
          if(!class_exists('Mustache_Engine')){
            add_action('admin_notices', array('VitalMustache', '__no_mustache'));
          } else {
            VitalMustache::initialize();
          }

        } else {
          add_action('admin_notices', array('VitalMustache', '__no_mustache'));
        }

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_link'));
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

    private static function initialize(){
      $opt = get_option('vital_mustache_option');
      $template_folder = $opt['vital_mustache_template_folder'];
      if($template_folder === FALSE){
        $template_folder = VitalMustache::$DEFAULT_TEMPLATE_FOLDER;
      }
      $template_folder_path = sprintf('%s/%s', get_template_directory(), $template_folder);
      $dir = realpath($template_folder_path);

      if(!$dir){
        add_action('admin_notices', array('VitalMustache', '__no_template_folder'));
      } else {
        VitalMustache::$engine = new Mustache_Engine(array(
          'loader' => new Mustache_Loader_FilesystemLoader($dir),
          'partials_loader' => new Mustache_Loader_FilesystemLoader($dir.'/partials'),
        ));
      }
    }

    /**
     * Add link to settings on Plugins page
     */
    public function add_action_link($links) {
        $custom_link = array(
           '<a href="' . admin_url('options-general.php?page=vital_mustache') . '">Settings</a>',
           );
        return array_merge($custom_link, $links);
    }

    public static function activate(){
        $mustache_path = plugin_dir_path(__FILE__) . '/lib/mustache/bin/build_bootstrap.php';

        ob_start();
        include_once($mustache_path);
        $result = ob_get_clean();
    }
}

register_activation_hook( __FILE__, array('VitalMustache', 'activate'));
new VitalMustache();

function render($template, $data, $return_string = false){
  $output = null;
  if($template && VitalMustache::$engine){
    $output = VitalMustache::$engine->render($template, $data);

    if(!$return_string){
      echo $output;
    }
  }

  return $output;
}