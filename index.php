<?php
/*
Plugin Name: Repeatable Meta Group
Plugin URI: http://rmg.cetainly.rocks
Description: Gives you an easy way to create repeatable meta field group
Version: 1.0
Author: Hasin Hayder
Author URI: http://hasin.me
License: GPL
Text Domain: rmg
*/

defined('ABSPATH') or die(__("Direct Access Is Not Allowed", "rmg"));

if (!class_exists("RepeatableMetaGroup")) {
    class RepeatableMetaGroup
    {
        private $display_default_metabox = 1;

        private $metabox = array(
            array(
                "name"       => "Sample MetaBox",
                "id"         => "rgm_smb",
                "post_types" => array("post","page"),
                "context"    => "normal",
                "priority"   => "default",
                "button"     => "Add More Options",
                "fields"     => array(
                    array(
                        "id"      => "field1",
                        "type"    => "text",
                        "name"    => "My Awesome Field 1",
                        "default" => ""
                    ),
                    array(
                        "id"      => "field3",
                        "type"    => "color",
                        "name"    => "My Color Field",
                        "default" => "#212121"
                    ),
                    array(
                        "id"      => "field2",
                        "type"    => "textarea",
                        "name"    => "My Awesome Field 2",
                        "default" => ""
                    ),
                    array(
                        "id"      => "field4",
                        "type"    => "select",
                        "name"    => "My Awesome Select 2",
                        "default" => "1",
                        "options" => array(
                            "1" => "Hello",
                            "2" => "World",
                            "3" => "Jupiter"
                        )
                    ),
                    array(
                        "id"      => "galgal",
                        "type"    => "gallery",
                        "name"    => "My Awesome Gallery",
                        "default" => ""
                    ),
                )
            )
        );

        function __construct() {
            register_activation_hook(__FILE__, array($this, "rmg_activate"));
            register_deactivation_hook(__FILE__, array($this, "rmg_deactivate"));

            add_action("init", array($this, "rmg_init"));
            add_action("admin_enqueue_scripts", array($this, "rmg_scripts"));

        }

        function rmg_activate() {

        }

        function rmg_deactivate() {

        }

        function rmg_scripts($hook) {
            if ($hook == "post.php" || $hook == "post-new.php") {
                wp_enqueue_style("rmg-admin", plugin_dir_url(__FILE__) . "css/admin.css");
                wp_enqueue_style("rmg-gallery", plugin_dir_url(__FILE__) . "css/rmg-gallery.css");
                wp_enqueue_script("rmg-admin-js", plugin_dir_url(__FILE__) . "js/rmg-admin.js", array("jquery", "farbtastic"), "0.1", true);
                wp_enqueue_script("rmg-gallery-js", plugin_dir_url(__FILE__) . "js/rmg-gallery.js", array("jquery"), "0.1", true);
            }
        }

        function rmg_init() {

            add_action("add_meta_boxes", array($this, "rmg_init_metaboxes"));
            add_action("save_post", array($this, "rmg_save_metaboxes"), 10, 2);
            add_action("edit_attachment", array($this, "rmg_save_metaboxes"), 10, 2);
        }

        function rmg_init_metaboxes() {

            $display_default_metabox = apply_filters("RMB_DISPLAY_DEFAULT_METABOX", $this->display_default_metabox);
            if ($display_default_metabox)
                $metaboxes = apply_filters("RMG_METABOXES", $this->metabox);
            else
                $metaboxes = apply_filters("RMG_METABOXES", array());

            if ($metaboxes) {
                foreach ($metaboxes as $mb) {
                    foreach($mb['post_types'] as $pt){
                        add_meta_box(
                            $mb['id'],      // Unique ID
                            esc_html__($mb['name'], 'rmg'),    // Title
                            array($this, 'rmg_draw_metaboxes'),   // Callback function
                            $pt,         // Admin page (or post type)
                            $mb['context'],         // Context
                            $mb['priority'],        // Priority
                            array("metabox" => $mb)
                        );
                    }

                }
            }

        }

        function rmg_draw_metaboxes($post, $args) {
            wp_nonce_field(basename(__FILE__), 'rmg_nonce');
            $metabox = $args['args']['metabox'];


            /**
             * find how many times it was repeated
             */

            $count = count(get_post_meta($post->ID, $metabox['fields']['0']['id'], true));
            if ($count == 0) $count = 1;

            echo "<div class='rmg'>";

            for ($i = 0; $i < $count; $i++) {
                echo "<div class='rmg-rb'>";
                echo "<div class='rmg-handle'><button class='button rmg-del'>X</button></div>";
                echo "<div class='rmg-fields'>";

                foreach ($metabox['fields'] as $field) {
                    $oldval = get_post_meta($post->ID, $field['id'], true);
                    if (isset($oldval[$i])) $field['default'] = $oldval[$i];
                    echo sprintf('<label for="%s_%d">%s</label>', $field['id'], $i, $field['name']);

                    switch ($field['type']) {
                        case "text":
                            echo sprintf('<input class="widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s_%d" value="%s"/><br/>', $field['type'], $field['type'], $field['id'], $field['id'], $i, $field['default']);
                            break;
                        case "color":
                            echo sprintf('<input class="rmg-color widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s_%d" value="%s"/><br/>', $field['type'], $field['type'], $field['id'], $field['id'], $i, $field['default']);
                            break;
                        case "textarea":
                            echo sprintf('<textarea class="data-fieldtype-%s" type="text" name="%s[]" id="%s_%d">%s</textarea><br/>', $field['type'], $field['id'], $field['id'], $i, $field['default']);
                            break;
                        case "wysywyg":
                            wp_editor($field['default'], "{$field['id']}_$i", array("textarea_name" => "{$field['id']}[]", "teeny" => true));
                            break;
                        case "select":
                            echo sprintf('<select name="%s[]" class="rmg-select data-fieldtype-%s" id="%s_%d">', $field['id'], $field['type'], $field['id'], $i);
                            echo "<option value=''>".__('Select a value','rmg')."</option>";
                            foreach ($field['options'] as $key => $val) {
                                $selected = "";
                                if ($field['default'] == $key) $selected = "selected";
                                echo sprintf('<option value="%s" %s>%s</option>', $key, $selected, $val);
                            }
                            echo "</select>";
                            break;
                        case "gallery":
                            echo "<ul class='gallery-ph'></ul>";
                            echo "<input class='galleryinfo'  name='".$field['id']."[]'  type='hidden' value='".$field['default']."'/>";
                            echo "<input type='button' data-multiple='true' value='Add Images To Gallery' class='galgal button button-primary button-large'>";
                            echo "<input type='button' value='Clear' style='margin-left:10px;' class='galgalremove button button-large' >";
                            break;
                    }
                }
                echo "</div>";
                echo "</div>";
                echo "<div style='clear:both'></div>";
            }

            if(!isset($metabox['button'])) $metabox['button']= __("Add More","rmg");
            echo "<div class='rmg-toolbar'><button class='button rmg-addmore'>{$metabox['button']}</button></div>";

            echo "</div>";

        }

        function rmg_save_metaboxes($pid, $post) {
            if (!isset($_POST['rmg_nonce']) || !wp_verify_nonce($_POST['rmg_nonce'], basename(__FILE__))) return $pid;
            $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            foreach ($_POST as $name => $val) {
                $oldval = get_post_meta($pid, $name, true);
                if (!$oldval || $oldval != $val) {
                    update_post_meta($pid, $name, $val);
                }
            }

            //now save formatted and grouped data
            $display_default_metabox = apply_filters("RMB_DISPLAY_DEFAULT_METABOX", $this->display_default_metabox);
            if ($display_default_metabox)
                $metaboxes = apply_filters("RMG_METABOXES", $this->metabox);
            else
                $metaboxes = apply_filters("RMG_METABOXES", array());

            foreach ($metaboxes as $mb) {
                $groupid = $mb['id'];
                $groupvals = $ungroupvals = $fields = array();
                $count = count(get_post_meta($pid, $mb['fields']['0']['id'], true));
                foreach ($mb['fields'] as $field) {
                    $ungroupvals[$field['id']] = get_post_meta($pid, $field['id'], true);
                    $fields[] = $field['id'];
                }

                for ($i = 0; $i < $count; $i++) {
                    $vals = array();
                    foreach ($fields as $field) {
                        $vals[$field] = $ungroupvals[$field][$i];
                    }

                    if (trim(join("", $vals)) != "" && trim(str_replace("#000000", "", join("", $vals))) != "")
                        $groupvals[] = $vals;
                }

                update_post_meta($pid, $mb['id'], $groupvals);
            }
        }

    }
}

new RepeatableMetaGroup();
