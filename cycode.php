<?php
/**
 * Plugin Name:       Cycode Test
 * Version:           0.0.1
 * Author:            Vladislav Unterberg
 */

defined( 'ABSPATH' ) || exit;

define('CYCODE_PLUGIN_DIR', __DIR__);

class Cycode_Plugin {
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activation']);
        register_deactivation_hook(__FILE__, [$this, 'deactivation']);

        add_action('admin_enqueue_scripts', [ $this, 'enqueue_sources'], 100);

        add_action('admin_menu', function(){
            add_menu_page(
                'Cycode Repository Information',
                'Cycode Rep Information',
                'manage_options',
                'cycode-repository-information',
                [$this, 'create_admin_page_handler'],
                '',
                4);
        } );
    }

    public function create_admin_page_handler() {
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>

            <form id="rep_info" method="GET">
                <input type="text" placeholder="test">
                <input type="submit">
            </form>

            <div id="response">

            </div>
        </div>
        <?php
    }

    public function enqueue_sources()
    {
        wp_enqueue_style('cycode-front-styles', plugin_dir_url( __FILE__ ) . '/assets/css/main.css', [], '1.0');
        wp_enqueue_script('cycode-admin-script', plugin_dir_url( __FILE__ ) . '/assets/js/admin.js', ['jquery'], '1.0', true);
    }

    public function activation(){}

    public function deactivation(){}

    public static function init()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
}

Cycode_Plugin::init();
