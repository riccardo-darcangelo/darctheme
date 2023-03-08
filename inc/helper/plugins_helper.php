<?php
// Activate installed plugin
function check_plugin_state($plugin){
    $activate_plugin_input_name = 'darctheme-activate-plugin-contact_' . $plugin['id'];

    if(file_exists(WP_PLUGIN_DIR . $plugin["path"])) {
        if (class_exists($plugin["class"])) {
            echo "<td><div class='setup-plugin-active'>Plugin ist aktiviert</div></td>";
        } else {
            ?>
            
            <td><p class='setup-plugin-inactive'>Plugin ist nicht aktiviert!</p></td>
            <td><input name="<?php echo $activate_plugin_input_name ?>" type="submit" value="aktivieren"></td>

            <?php
            if(isset($_POST[$activate_plugin_input_name])) {
                $plugin_path = trim( $plugin["path"] );
                $current = get_option( 'active_plugins' );
                $plugin_path = plugin_basename( $plugin_path );

                if ( !in_array( $plugin_path, $current ) ) {
                    $current[] = $plugin_path;
                    sort( $current );
                    do_action( 'activate_plugin', $plugin_path );
                    update_option( 'active_plugins', $current );
                    do_action( 'activate_' . $plugin_path );
                    do_action( 'activated_plugin', $plugin_path );
                }

                echo "<meta http-equiv='refresh' content='0'>";
            }
        }
    }
    else {
        darctheme_get_plugin($plugin);
    }
}


// Activate download and plugin
function darctheme_get_plugin($plugin) {
    $plugin_dir = ABSPATH.'wp-content/plugins/';
    $plugin_input_name = 'darctheme-install-plugin-contact_' . $plugin['id'];

    ?>
                                    
    <td><p class='setup-plugin-inactive'>Plugin ist nicht installiert!</p></td>
    <td><input name="<?php echo $plugin_input_name ?>" type="submit" value="installieren"></td>

    <?php
    if(isset($_POST[$plugin_input_name])) {   
        darctheme_plugin_download($plugin['url'], $plugin_dir.$plugin['name'].'.zip');
        darctheme_plugin_unpack($plugin_dir, $plugin_dir.$plugin['name'].'.zip');
        darctheme_plugin_activate($plugin['path']);
        echo "<meta http-equiv='refresh' content='0'>";
    }
}
function darctheme_plugin_download($url, $path) 
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    if(file_put_contents($path, $data))
            return true;
    else
            return false;
}
function darctheme_plugin_unpack($pathto, $target) {
    $zip = new ZipArchive;
    if ($zip->open($target) === TRUE) {
        $zip->extractTo($pathto);
        $zip->close();
    } else {
        echo 'Upss... Bei der Installation ist etwas schief gelaufen';
    }
}
function darctheme_plugin_activate($installer)
{
    $current = get_option('active_plugins');
    $plugin = plugin_basename(trim($installer));

    if(!in_array($plugin, $current))
    {
            $current[] = $plugin;
            sort($current);
            do_action('activate_plugin', trim($plugin));
            update_option('active_plugins', $current);
            do_action('activate_'.trim($plugin));
            do_action('activated_plugin', trim($plugin));
            return true;
    }
    else
            return false;
}