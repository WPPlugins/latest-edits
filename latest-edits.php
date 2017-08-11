<?php
/*
Plugin Name: Latest edits
Plugin URI: http://www.mansjonasson.se/latest-edits/
Description: Adds a widget in your Dashboard which shows a list of the latest pages/posts edits, so you can easily find where you were working last. 
Version: 1.0
Author: Måns Jonasson
Tags: admin, dashboard, widget
Requires at least: 2.7
Tested up to: 2.8.4
Author URI: http://www.mansjonasson.se

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html

Developed for .SE (Stiftelsen för Internetinfrastruktur) - http://www.iis.se

Some code borrowed from Dashboard Post-it (http://wordpress.org/extend/plugins/dashboard-post-it/) Thanks!

*/

function latest_edits() {
	global $wpdb;
	$widget_options = latest_edits_options();
	
	echo "<link rel='stylesheet' href='" . get_bloginfo("wpurl") . "/wp-content/plugins/latest-edits/latest-edits.css' />\n";
	
	// Define DB table names
	$table_name = $wpdb->prefix . "posts";
	$user_table_name = $wpdb->prefix . "users";
	$sql = "SELECT DISTINCT p.ID, p.post_title, p.post_author, u.display_name, p.post_modified, p.post_type FROM $table_name p INNER JOIN $user_table_name u ON u.ID = p.post_author WHERE (p.post_type = 'post' OR p.post_type = 'page') ORDER BY p.post_modified DESC LIMIT 0, {$widget_options["latest_edits_count"]}";
	$rs = mysql_query($sql);
	
	echo "<table id=\"latest_edits\">\n";
	echo "<tr><th>Title</th><th>Type</th><th>Edited</th><th>By</th></tr>\n";
	
	while($rows = mysql_fetch_assoc($rs)) {
		
		// Get latest revision for this post
		
		$sql = "SELECT p.ID, u.display_name FROM $table_name p INNER JOIN $user_table_name u ON u.ID = p.post_author WHERE RIGHT(p.post_name, 8) != 'autosave' AND p.post_parent = '{$rows["ID"]}' AND p.post_type = 'revision' ORDER BY p.post_modified DESC LIMIT 0, 1";
		$rs2 = mysql_query($sql);
		#list($userID, $userName) = mysql_fetch_assoc($rs2);
		$rows2 = mysql_fetch_assoc($rs2);
		
		echo "<tr>\n";
		echo "<td>";
		echo "<a href=\"" . get_bloginfo('wpurl') . "/wp-admin/";
		if ($rows["post_type"] == "page") {
			echo "page.php";
		}
		else {
			echo "post.php";	
		}
		echo "?action=edit&post={$rows["ID"]}\">";
		echo __($rows["post_title"]);
		echo "</a> <a href=\"" . get_permalink($rows["ID"]) . "\">#</a>";
		echo "</td>\n";
		echo "<td>{$rows["post_type"]}</td>\n";
		echo "<td>{$rows["post_modified"]}</td>\n";
		echo "<td>{$rows2["display_name"]}</td>\n";
	}
	
	
	echo "</table>\n";
}
 

function latest_edits_options() {

	$defaults = array( 'latest_edits_title' => 'Latest edits', 'latest_edits_count' => 10);
	if ( ( !$options = get_option( 'latest_edits' ) ) || !is_array($options) )
		$options = array();
	return array_merge( $defaults, $options );
	global $defaults;
}

function latest_edits_init() {
	$options = latest_edits_options();
	$title = $options['latest_edits_title'];
	wp_add_dashboard_widget( 'latest_edits', $title, 'latest_edits', 'latest_edits_setup' );
}

function latest_edits_setup() {
 
	$options = latest_edits_options();
 
	// Get the form data
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) && isset( $_POST['widget_id'] ) && 'latest_edits' == $_POST['widget_id'] ) {
		foreach ( array( 'latest_edits_title', 'latest_edits_count' ) as $key )
				$options[$key] = stripslashes($_POST[$key]);
				if ( !current_user_can('unfiltered_html') )
					$newoptions['text'] = stripslashes(wp_filter_post_kses($newoptions['text'])); // This should take care of HTML permissions.
		update_option( 'latest_edits', $options );
	}
		
?>
		<label style="display:block;margin-bottom:4px;" for="latest_edits_title">Widget title: <input type="text" id="latest_edits_title" name="latest_edits_title" value="<?php echo $options['latest_edits_title'] ?>" /></label>
		<label style="display:block;margin-bottom:4px;" for="latest_edits_count">Number of edits to show: <input type="text" id="latest_edits_count" name="latest_edits_count" value="<?php echo $options['latest_edits_count'] ?>" /></label>
<?php
}
 
/**
 * Hook it in
 */
add_action('wp_dashboard_setup', 'latest_edits_init');
 
?>