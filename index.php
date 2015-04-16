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

defined( 'ABSPATH' ) or die( __( "Direct Access Is Not Allowed", "rmg" ) );

if ( ! class_exists( "RepeatableMetaGroup" ) ) {
	class RepeatableMetaGroup {
		private $display_default_metabox = 1;

		private $metabox = array(
			array(
				"name"           => "Sample MetaBox",
				"id"             => "rgm_smb",
				"post_types"     => array( "post", "page" ),
				"context"        => "normal",
				"priority"       => "default",
				"button"         => "Add More Options",
				"page_templates" => array(),
				"post_formats"   => array(),
				"post_ids"       => array(),
				"fields"         => array(
					array(
						"id"      => "field1",
						"type"    => "text",
						"name"    => "My Awesome Field 1",
						"default" => ""
					),
					array(
						"id"      => "field1c",
						"type"    => "checkbox",
						"name"    => "My Awesome Field 1",
						"default" => "1"
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
						"id"      => "field4r",
						"type"    => "radio",
						"name"    => "My Awesome Radio 2",
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
			register_activation_hook( __FILE__, array( $this, "rmg_activate" ) );
			register_deactivation_hook( __FILE__, array( $this, "rmg_deactivate" ) );

			add_action( "init", array( $this, "rmg_init" ) );
			add_action( "admin_enqueue_scripts", array( $this, "rmg_scripts" ), 1000 );

		}

		function rmg_activate() {

		}

		function rmg_deactivate() {

		}

		function rmg_scripts( $hook ) {

			if ( strpos( plugin_dir_url( __FILE__ ), plugin_dir_path( __FILE__ ) ) !== false ) {
				//loaded from theme
				$path = dirname( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, __FILE__ ) );
			} else {
				//loaded as plugin
				$path = plugin_dir_url( __FILE__ );
			}

			if ( $hook == "post.php" || $hook == "post-new.php" ) {

				$button_labels = array(
					"multiple" => __( "Customize This Gallery", "rmg" ),
					"single"   => __( "Change Image", "rmg" ),
				);

				wp_enqueue_style( "rmg-admin", $path . "/css/admin.css", null, "1.0" );
				wp_enqueue_style( "rmg-gallery", $path . "/css/rmg-gallery.css" );
				wp_enqueue_script( "rmg-admin-js", $path . "/js/rmg-admin.js", array(
						"jquery",
						"farbtastic"
					), "0.1", true );
				wp_enqueue_script( "rmg-gallery-js", $path . "/js/rmg-gallery.js", array( "jquery" ), "0.1", true );
				wp_localize_script( "rmg-gallery-js", "rmggal", $button_labels );
			}
		}

		function rmg_init() {

			add_action( "add_meta_boxes", array( $this, "rmg_init_metaboxes" ) );
			add_action( "save_post", array( $this, "rmg_save_metaboxes" ), 10, 2 );
			add_action( "edit_attachment", array( $this, "rmg_save_metaboxes" ), 10, 2 );
		}

		function rmg_init_metaboxes() {

			$display_default_metabox = apply_filters( "rmg_display_default_metabox", $this->display_default_metabox );
			if ( $display_default_metabox ) {
				$metaboxes = apply_filters( "rmg_metaboxes", $this->metabox );
			} else {
				$metaboxes = apply_filters( "rmg_metaboxes", array() );
			}

			if ( $metaboxes ) {
				foreach ( $metaboxes as $mb ) {
					foreach ( $mb['post_types'] as $pt ) {
						add_meta_box(
							$mb['id'],      // Unique ID
							esc_html__( $mb['name'], 'rmg' ),    // Title
							array( $this, 'rmg_draw_metaboxes' ),   // Callback function
							$pt,         // Admin page (or post type)
							$mb['context'],         // Context
							$mb['priority'],        // Priority
							array( "metabox" => $mb )
						);
					}

				}
			}

		}

		function rmg_draw_metaboxes( $post, $args ) {

			wp_nonce_field( basename( __FILE__ ), 'rmg_nonce' );
			$metabox = $args['args']['metabox'];

			/**
			 * Visibility Conditions
			 */
			$page_templates = "";
			if ( isset( $metabox['page_templates'] ) ) {
				$page_templates = join( ",", $metabox['page_templates'] );
			}

			$post_formats = "";
			if ( isset( $metabox['post_formats'] ) ) {
				$post_formats = join( ",", $metabox['post_formats'] );
			}


			/**
			 * find how many times it was repeated
			 */

			$count = count( get_post_meta( $post->ID, $metabox['fields']['0']['id'], true ) );
			if ( $count == 0 ) {
				$count = 1;
			}

			echo "<div class='rmg' data-page-templates='{$page_templates}' data-post-formats='{$post_formats}'>";

			for ( $i = 0; $i < $count; $i ++ ) {
				echo "<div class='rmg-rb'>";
				echo "<div class='rmg-handle'><button class='button rmg-del'>X</button><button class='button rmg-up'><</button><button class='button rmg-down'>></button></div>";
				echo "<div class='rmg-fields'>";

				$j=0;
				foreach ( $metabox['fields'] as $field ) {
					$jsfields[ $field['id'] ] = 1;
					$j+=1;;
					$oldval                   = get_post_meta( $post->ID, $field['id'], true );
					if ( $field['type'] != "checkbox" ) {
						$value = isset( $oldval[ $i ] ) ? $oldval[ $i ] : $field['default'];
					}elseif ( $field['type'] != "radio" ) {
						$value = isset( $oldval[ $i ] ) ? $oldval[ $i ] : $field['default'];
					}else {
						$value = $oldval[ $i ];
					}

					echo "<table class='meta-table'>";

					echo sprintf( '<tr><th width="100"><label for="%s---%d">%s</label></th><td align="left">', $field['id'], $i, $field['name'] );

					switch ( $field['type'] ) {
						case "text":
							echo sprintf( '<input class="widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s---%d" value="%s"/><br/>', $field['type'], $field['type'], $field['id'], $field['id'], $i, $value );
							break;
						case "radio":
							foreach ( $field['options'] as $fo=>$label ) {
								$rchecked = "";

								echo $fo.":".$value;

								if($fo==$value) $rchecked = "checked";
								echo sprintf( '<input class="widefat data-fieldtype-%s" type="%s" name="%s---%d[]" data-counter="%d" value="%s" %s/>', $field['type'], $field['type'], $field['id'],$i, $i, $fo, $rchecked );
								echo "<label class='rl'>{$label}</label>";
							}
							echo sprintf( '<input class="widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s---%d" value="%s" />', "hidden", "hidden", $field['id'], $field['id'], $i, $value );
							break;
						case "checkbox":
							$checked = "";
							if ( $value ) {
								$checked = "checked";
							}
							echo sprintf( '<input class="widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s---%d" value="%s" />', $field['type'], "hidden", $field['id'], $field['id'], $i, $value );
							echo sprintf( '<input class="widefat data-fieldtype-%s" type="%s" data-index="%d" id="c___%s---%d" value="%s" %s /><br/>', $field['type'], $field['type'], $i, $field['id'], $i, $field['default'], $checked );
							break;
						case "color":
							echo sprintf( '<input class="rmg-color widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s---%d" value="%s"/><br/>', $field['type'], $field['type'], $field['id'], $field['id'], $i, $value );
							break;
						case "textarea":
							echo sprintf( '<textarea class="data-fieldtype-%s" type="text" name="%s[]" id="%s---%d">%s</textarea><br/>', $field['type'], $field['id'], $field['id'], $i, $value );
							break;
						case "wysywyg":
							wp_editor( $value, "{$field['id']}_$i", array(
									"textarea_name" => "{$field['id']}[]",
									"teeny"         => true
								) );
							break;
						case "select":
							echo sprintf( '<select name="%s[]" class="rmg-select data-fieldtype-%s" id="%s---%d">', $field['id'], $field['type'], $field['id'], $i );
							echo "<option value=''>" . __( 'Select a value', 'rmg' ) . "</option>";
							foreach ( $field['options'] as $key => $val ) {
								$selected = "";
								if ( $value == $key ) {
									$selected = "selected";
								}
								echo sprintf( '<option value="%s" %s>%s</option>', $key, $selected, $val );
							}
							echo "</select>";
							break;
						case "gallery":
							echo "<ul class='gallery-ph'></ul>";
							echo "<input class='galleryinfo'  name='" . $field['id'] . "[]'  type='hidden' value='" . $value . "'/>";
							echo "<input type='button' data-multiple='true' value='" . __( 'Add Images To Gallery', 'rmg' ) . "' class='galgal button button-primary button-large'>";
							echo "<input type='button' value='" . __( 'Clear', 'rmg' ) . "' style='margin-left:10px;' class='galgalremove button button-large' >";
							break;
						default:
							echo sprintf( '<input class="widefat data-fieldtype-%s" type="%s" name="%s[]" id="%s---%d" value="%s"/><br/>', "text", "text", $field['id'], $field['id'], $i, $value );
							break;

					}

					echo "</td></tr></table>";
				}
				echo "</div> <!--rmg fields-->";
				echo "</div> <!--rmg rb-->";
				//echo "<div style='clear:both'></div>";
			}

			if ( ! isset( $metabox['button'] ) ) {
				$metabox['button'] = __( "Add More", "rmg" );
			}

			echo "<div class='rmg-toolbar'><button class='button rmg-addmore'>{$metabox['button']}</button></div>";

			echo "</div>";

		}

		function rmg_save_metaboxes( $pid, $post ) {
			if ( ! isset( $_POST['rmg_nonce'] ) || ! wp_verify_nonce( $_POST['rmg_nonce'], basename( __FILE__ ) ) ) {
				return $pid;
			}
			$_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			foreach ( $_POST as $name => $val ) {
				$oldval = get_post_meta( $pid, $name, true );
				if ( ! $oldval || $oldval != $val ) {
					update_post_meta( $pid, $name, $val );
				}
			}

			//now save formatted and grouped data
			$display_default_metabox = apply_filters( "rmg_display_default_metabox", $this->display_default_metabox );
			if ( $display_default_metabox ) {
				$metaboxes = apply_filters( "rmg_metaboxes", $this->metabox );
			} else {
				$metaboxes = apply_filters( "rmg_metaboxes", array() );
			}

			foreach ( $metaboxes as $mb ) {
				$groupid   = $mb['id'];
				$groupvals = $ungroupvals = $fields = array();
				$count     = count( get_post_meta( $pid, $mb['fields']['0']['id'], true ) );
				foreach ( $mb['fields'] as $field ) {
					$ungroupvals[ $field['id'] ] = get_post_meta( $pid, $field['id'], true );
					$fields[]                    = $field['id'];
				}

				for ( $i = 0; $i < $count; $i ++ ) {
					$vals = array();
					foreach ( $fields as $field ) {
						$vals[ $field ] = $ungroupvals[ $field ][ $i ];
					}

					if ( trim( join( "", $vals ) ) != "" && trim( str_replace( "#000000", "", join( "", $vals ) ) ) != "" ) {
						$groupvals[] = $vals;
					}
				}

				update_post_meta( $pid, $mb['id'], $groupvals );
			}
		}

	}
}

new RepeatableMetaGroup();

