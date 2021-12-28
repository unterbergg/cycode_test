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

        add_action( 'wp_ajax_get_repos', [$this, 'get_repos'], 99 );
    }

    public function get_repos() {

        $org = $_POST['org'];
        $per_page = $_POST['per_page'];
        $page = $_POST['page'];

        $ch = curl_init();

        $header = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: token '. "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";

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/orgs/{$org}");
        $all = curl_exec($ch);
        $all = json_decode($all, true);

        if($all['message']) {
            echo $all['message'];
        } else {

            curl_setopt($ch, CURLOPT_URL, "https://api.github.com/orgs/{$org}/repos?per_page={$per_page}&page={$page}");
            $json = curl_exec($ch);


            echo "<section class='response-wrap'>";
            ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Profile page</th>
                    <th>Description</th>
                    <th>Size</th>
                    <th>Language</th>
                    <th>Contributors</th>
                </tr>
                <?php
                foreach (json_decode($json, true) as $key => $item) {
                    echo "<tr>";
                    echo "<td>";
                    echo $item['name'];
                    echo "</td>";
                    echo "<td>";
                    echo $item['owner']['html_url'];
                    echo "</td>";
                    echo "<td>";
                    echo $item['description'];
                    echo "</td>";
                    echo "<td>";
                    echo $item['size'];
                    echo "</td>";
                    echo "<td>";
                    echo $item['language'];
                    echo "</td>";

                    curl_setopt($ch, CURLOPT_URL, $item['contributors_url']);
                    $contributors = curl_exec($ch);
                    $contributors = json_decode($contributors, true);
                    echo "<td>";
                    foreach ($contributors as $contributor) {
                        echo "<a href='#'>";
                        echo $contributor['login'];
                        echo "</a>";
                        echo PHP_EOL;
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <?php
            echo "</section>";

            $this->render_pagination($all['public_repos'], $per_page, $page);
        }
        curl_close($ch);

        die();
    }

    public function render_pagination($repos, $per_page, $page) {

        $pages = ceil($repos / $per_page);
        $radius = 2;

        $html = "<div class='pagination'>";
        
        if($pages > 5) {
            $start = $page - 2;
            if ($start <= 0) {
                $start = 1;
                $end = 5;
            } else {
                $end = $page + 2;
                if ($end > $pages) {
                    $end = $pages;
                    $start = $page - 5;
                }
            }
        
            if ($start != 1) {
                $html .= '<span class="empty-space"> ... </span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $html .= "<span><button class='button' data-page='{$i}'>{$i}</button></span>";
            }

            if ($end != $pages) {
                $html .= '<span class="empty-space"> ... </span>';
            }
        } else {
            for ($i = 1; $i <= $pages; $i++) {
                $html .= "<span><button class='button' data-page='{$i}'>{$i}</button></span>";
            }
        }

        $html .= "</div>";

        if ($pages > 1) {
            echo $html;
        }
    }

    public function create_admin_page_handler() {
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>

            <form id="rep_info" method="GET">
                <input type="text" id="org" placeholder="Organization">
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
        wp_localize_script( 'cycode-admin-script', 'globalVars', array(
            'page' => 1,
            'all_pages' => 0,
            'url' => admin_url('admin-ajax.php')
        ) );
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
