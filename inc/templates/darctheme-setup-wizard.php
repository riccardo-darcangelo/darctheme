<?php
require (get_template_directory() . '/inc/helper/plugins_helper.php');
$plugins_raw = file_get_contents(get_template_directory_uri() . '/inc/needed_plugins.json');
$plugins = json_decode($plugins_raw,true);
?>

<div class="setup_wizard_wrapper">
    <div class="setup_wizard_inner">
        <div class="setup_wizard_titles">
            <h1>Setup Wizard</h1>
            <h3>Benötigte Plugins</h3>
        </div>
        <div class="plugin-wrapper">
            <div class="single-plugin">
                <table>
                    <thead>
                        <tr>
                            <th>Plugin</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($plugins as $plugin) { ?>
                            <tr><?php
                                echo "<td><p>" . $plugin["name"] . ": </p></td>";
                                ?>

                                <form method="post"> <?php
                                    check_plugin_state($plugin);
                                ?></form>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>