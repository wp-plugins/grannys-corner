<?php
/*
Plugin Name: Grannys corner
Description: Text widget for Granny's column
Version: 1.6
Author: J.N. Breetvelt a.k.a OpaJaap
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/grannys-corner
*/

load_plugin_textdomain('grc', 'wp-content/plugins/grannys-corner/langs/', 'grannys-corner/langs/');
/**
 * GrannysCorner Class
 */
class GrannysCorner extends WP_Widget {

    /** constructor */
    function __construct() {
		$widget_ops = array( 'classname' => 'widget_grannys_corner', 'description' => __( 'Granny\'s column text widget', 'grc') );	
		parent::__construct( 'grannys_corner', __( 'Granny\'s Corner', 'grc' ), $widget_ops );
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $widget_content;

        extract( $args );
        
 		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Granny\'s Corner', 'grc' ) : $instance['title']);
		if (is_user_logged_in()) {
			$itsme = $this->get_user() == $instance['user'];
		}
		else $itsme = false;

		$widget_option = $this->get_widget_option( $instance['user'] );
		
		$updated = false;
		if ($itsme) {
			if (isset($_GET['grc-txt-'.$this->get_widget_id()])) {
				update_option( $widget_option, strip_tags( html_entity_decode( $_GET['grc-txt-'.$this->get_widget_id()] ), '<a><b><br><i><span>' ) );
				$updated = true;
			}
		}

		$the_text = stripslashes( get_option( $widget_option ) );
		
		if ($itsme && !$updated) {
			$widget_content = '<form action="'.get_option('siteurl').'" method="get" >';
			$widget_content .= '<textarea name="grc-txt-'.$this->get_widget_id().'" id="grc-text" style="width:95%; height:250px;">'.$the_text.'</textarea>';
			$widget_content .= '<input type="submit" class="button-primary" name="grc-submit" value="'.__('Save', 'grc').'" />';
			$widget_content .= '<input type="reset" onclick="document.getElementById(\'grc-text\').innerHTML=\'\'" class="button-primary" name="grc-reset" value="'.__('Clear', 'grc').'" />';
			$widget_content .= '</form>';
//			$widget_content .= '<script type="text/javascript">elm=document.getElementById("grc-text");elm.value = "'.esc_js($the_text).'";</script>';
		}
		else {
			$widget_content = '<style>p{margin:12px 0 0 0;}</style>'.wpautop(__($the_text));

		}

		echo $before_widget . $before_title . $title . $after_title . $widget_content . $after_widget;
    }
	

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['user'] = $new_instance['user'];

        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'user' => '') );
		$title = esc_attr( $instance['title'] );
		$user = $instance['user'];
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'grc'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('user'); ?>"><?php _e( 'User:', 'grc' ); ?></label>	
			<select name="<?php echo $this->get_field_name('user'); ?>" id="<?php echo $this->get_field_id('user'); ?>" class="widefat">
<?php
			$this->user_select($user);
?>
			</select>
		</p>
<?php
    }

	function get_user() {
	global $current_user;
		get_currentuserinfo();
		$user = $current_user->user_login;
		return $user;
	}

	function get_users() {
	global $wpdb;
		$users = $wpdb->get_results('SELECT * FROM '.$wpdb->users, 'ARRAY_A');
		return $users;
	}

	function user_select($select = '') {
		$result = '';
		$iam = $select == '' ? $this->get_user() : $select;
		$users = $this->get_users();
		foreach ($users as $usr) {
			if ($usr['user_login'] == $iam) $sel = 'selected="selected"';
			else $sel = '';
			$result .= '<option value="'.$usr['user_login'].'" '.$sel.'>'.$usr['display_name'].'</option>';
		}	
		echo ($result);
	}
	
	function get_widget_id() {
		$widgetid = substr( $this->get_field_name( 'txt' ), strpos( $this->get_field_name( 'txt' ), '[' ) + 1 );
		$widgetid = substr( $widgetid, 0, strpos( $widgetid, ']' ) );
		return $widgetid;
	}
	
	function get_widget_option($user) {
		$widgetid = $this->get_widget_id();
		return 'grannys-corner-'.$user.'-'.$widgetid;
	}
} // class GrannysCorner

// register GrannysCorner widget
add_action('widgets_init', create_function('', 'return register_widget("GrannysCorner");'));
?>
