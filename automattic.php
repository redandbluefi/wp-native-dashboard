<?php

if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

class wp_native_dashboard_automattic {
	function wp_native_dashboard_automattic($tagged, $root_tagged) {
		$this->tagged_version 		= $tagged;
		$this->root_tagged_version 	= $root_tagged;
		add_action('admin_head', array(&$this, 'on_admin_head'));
		add_action('wp_ajax_wp_native_dashboard_check_repository', array(&$this, 'on_ajax_wp_native_dashboard_check_repository'));
		add_action('wp_ajax_wp_native_dashboard_check_language', array(&$this, 'on_ajax_wp_native_dashboard_check_language'));
		add_action('wp_ajax_wp_native_dashboard_delete_language', array(&$this, 'on_ajax_wp_native_dashboard_delete_language'));
		add_action('wp_ajax_wp_native_dashboard_download_language', array(&$this, 'on_ajax_wp_native_dashboard_download_language'));
	}
	
	function on_admin_head() {
		?>
		<script  type="text/javascript">
		//<![CDATA[
		function wpnd_delete_language() {
			var elem = jQuery(this);
			jQuery.ajax({
				type: "POST",
				url: "admin-ajax.php",
				data: { action: 'wp_native_dashboard_delete_language', file : jQuery(this).attr('href') },
				success: function(msg){
					elem.parents('tr').fadeOut('slow', function() { jQuery(this).remove(); });
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					//handled in next version that also support all file system types
				}
			});
			return false;			
		}
		var last_auto_row = 0;
		function analyse_automattic_repository(idx) {
			if (idx<wp_native_dashboard_repository.entries) {
				if (idx==0) {
					last_auto_row = 0;
					jQuery('#svn-downloads .progressbar>div>div').css({ 'width' : '0%' });
					jQuery('#svn-downloads .progressbar div div div').html('0&nbsp;%');
					jQuery('#svn-downloads .progressbar').show();
					jQuery('#csp-check-repository').parent().find('.ajax-feedback').css({visibility : 'visible' });
				}
				jQuery.post("admin-ajax.php", { action: 'wp_native_dashboard_check_language', language: wp_native_dashboard_repository.langs[idx], row: last_auto_row },
					function(data) {
						if (data != '')	{ 
							jQuery('#table_svn_i18n>tbody').append(data);
							last_auto_row += 1;
						}
						var perc = Math.min((idx+1)*100.0 / wp_native_dashboard_repository.entries, 100.0);
						jQuery('#svn-downloads .progressbar>div>div').css({ 'width' : perc + '%' });
						jQuery('#svn-downloads .progressbar div div div').html(Math.round(perc)+'&nbsp;%');
						window.setTimeout('analyse_automattic_repository('+(idx+1)+')', 50);
					}
				)
			}
			else{
				jQuery('#table_svn_i18n').show();
				jQuery('#svn-downloads .progressbar').hide();
				jQuery('#csp-check-repository').show();
				jQuery('#csp-check-repository').parent().find('.ajax-feedback').css({visibility : 'hidden' });
				jQuery('.csp-download-svn-file').click(function() {
					var elem = jQuery(this);
					elem.parent().find('.ajax-feedback').css({visibility : 'visible' });
					jQuery.ajax({
						type: "POST",
						url: "admin-ajax.php",
						data: { action: 'wp_native_dashboard_download_language', file : elem.attr('href') },
						success: function(msg){
							jQuery('#table_local_i18n').append(msg);
							jQuery('#table_local_i18n tr:last .csp-delete-local-file').click(wpnd_delete_language);
							elem.parents('tr').fadeOut('slow', function() { elem.remove(); });
							elem.parent().find('.ajax-feedback').css({visibility : 'hidden' });
							csl_refresh_language_switcher();
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
							//handled in next version that also support all file system types
							elem.parent().find('.ajax-feedback').css({visibility : 'hidden' });
						}
					});
					return false;
				});
			}
		}
		jQuery(document).ready(function($) { 
			$('#csp-check-repository').click(function() {
				jQuery('#table_svn_i18n').hide();
				var self = $(this);
				self.parent().find('.ajax-feedback').css({visibility : 'visible' });
				$.post("admin-ajax.php", { action: 'wp_native_dashboard_check_repository' },
					function(data) {
						self.parent().find('.ajax-feedback').css({visibility : 'hidden' });
						$(document.body).append(data);
					}
				)
				return false;
			});
			$('.csp-delete-local-file').click(wpnd_delete_language);
		});
		//]]>
		</script>
		<?php
	}
	
	function on_ajax_wp_native_dashboard_check_repository() {
		$installed = wp_native_dashboard_collect_installed_languages();
		$revision 	= 0;
		$langs 		= $installed;
		$url 		= 'http://svn.automattic.com/wordpress-i18n/';
		$response = wp_remote_get($url);
		$error = is_wp_error($response);
		if(!$error) {
			$lines = split("\n",$response['body']);
			foreach($lines as $line) {
				if (preg_match("/href\s*=\s*\"(\S+)\/\"/", $line, $hits)) {
					if (in_array($hits[1], array('tools', 'theme', 'pot', 'http://subversion.tigris.org'))) continue;
					if (preg_match("/@/", $hits[1])) continue;
					if (!in_array($hits[1], $langs)) $langs[] = $hits[1];
				}
			}
			sort($langs);
		}
		?>
		<script type="text/javascript">
		//<![CDATA[
		var wp_native_dashboard_repository = {
			error: "<?php if(!$error) echo __('The network connection to <strong>svn.automattic.com</strong> is currently not available. Please try again later.', 'wp-native-dashboard'); ?>",
			entries: <?php echo count($langs); ?>,
			langs : ["<?php echo implode('","', $langs); ?>"]
		}
		if(wp_native_dashboard_repository.error.length!=0) {
			jQuery('#csp-check-repository').hide();
			jQuery('#table_svn_i18n tbody').html('');
			analyse_automattic_repository(0);
		}
		else {
			jQuery('#csp-check-repository').hide();
			jQuery('#table_svn_i18n tbody').html('<tr><td align="center">'+wp_native_dashboard_repository.error+'</td></tr>').parent().show();
		}
		//]]>
		</script>
		<?php
		exit();
	}
	
	function on_ajax_wp_native_dashboard_check_language() {
		$lang 			= $_POST['language'];
		$row 			= $_POST['row'];
		$installed 		= wp_native_dashboard_collect_installed_languages();
		$url 			= "http://svn.automattic.com/wordpress-i18n/".$lang."/tags/".$this->tagged_version."/messages/";
		$url_root		= "http://svn.automattic.com/wordpress-i18n/".$lang."/tags/".$this->root_tagged_version."/messages/";
		$response_mo 	= wp_remote_get($url);
		$found 			= false;
		$tagged			= $this->tagged_version;
		
		if (!is_wp_error($response_mo)){
			if (preg_match("/href\s*=\s*\"".$lang."\.mo\"/", $response_mo['body'])) 
				$found = true;
		}
		if ($found === false) {
			$url = $url_root;
			$tagged	= $this->root_tagged_version;
			$response_mo = wp_remote_get($url);
			if (!is_wp_error($response_mo)){
				if (preg_match("/href\s*=\s*\"".$lang."\.mo\"/", $response_mo['body'])) 
					$found = true;
			}
		}
		if ($found === false) exit();
		$url .= $lang.'.mo';
		?>
		<tr id="tr-i18n-download-<?php echo $lang; ?>" class="<?php if (($row + 1) % 2) echo 'alternate'; ?>">
		<td><span class="i18n-file csp-<?php echo $lang; ?>"><?php echo $lang; ?></span></td>
		<td><?php echo (wp_native_dashboard_is_rtl_language($lang) ? __('right to left', 'wp-native-dashboard') : '&nbsp;'); ?></td>
		<td>-n.a.-</td>
		<td><?php if(!in_array($lang, $installed)) : ?>
				<a class="csp-download-svn-file" href="<?php echo $url; ?>"><?php _e('Download','wp-native-dashboard'); echo '&nbsp;('.$tagged.')'; ?></a>&nbsp;<span><img src="images/loading.gif" class="ajax-feedback" title="" alt="" /></span>
			<?php else: echo '&nbsp;'; endif; ?>
		</td>
		</tr>
		<?php 
		exit();
	}
	
	function on_ajax_wp_native_dashboard_delete_language() {
		if (is_user_logged_in() && current_user_can('manage_options')) {
			global $wp_filesystem;
			$file = basename($_POST['file']);
			if (file_exists(WP_CONTENT_DIR.'/languages/'.$file)) {
				ob_start();
				if ( WP_Filesystem(true) && is_object($wp_filesystem) ) {
					if($wp_filesystem->delete(WP_CONTENT_DIR.'/languages/'.$file)) {
						$wp_filesystem->delete(WP_CONTENT_DIR.'/languages/'.substr($file, 0, -2).'php');
						$wp_filesystem->delete(WP_CONTENT_DIR.'/languages/continents-cities-'.$file);
						ob_end_clean();
						exit();
					}
				}
				ob_end_clean();
			}
		}
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You do not have the permission to delete language files.", 'wp-native-dashboard');
		exit();
	}
	
	function on_ajax_wp_native_dashboard_download_language() {
		if (is_user_logged_in() && current_user_can('manage_options')) {
			global $wp_filesystem;
			$file = basename($_POST['file']);
			$lang = substr($file,0,-3);
			$tagged = $this->tagged_version;
			if (preg_match('/\/tags\/(\d+\.\d+|\d+\.\d+\.\d+)\/messages/', $_POST['file'], $h)) {
				$tagged = $h[1];
			}
			$response_mo = wp_remote_get("http://svn.automattic.com/wordpress-i18n/".$lang."/tags/".$tagged."/messages/".$file);
			if(!is_wp_error($response_mo)) {
				ob_start();
				if ( WP_Filesystem(true) && is_object($wp_filesystem) ) {
					$done = $wp_filesystem->put_contents(WP_CONTENT_DIR.'/languages/'.$file, $response_mo['body'], FS_CHMOD_FILE);
					if ($done) {
						global $wp_version;
						if (version_compare($wp_version, '2.8', '>=')) {
							$response_cities_mo = wp_remote_get("http://svn.automattic.com/wordpress-i18n/".$lang."/tags/".$tagged."/dist/wp-content/languages/continents-cities-".$file);
							if(!is_wp_error($response_cities_mo)) {
								$wp_filesystem->put_contents(WP_CONTENT_DIR.'/languages/continents-cities-'.$file, $response_cities_mo['body'], FS_CHMOD_FILE);
							}
						}
						if (wp_native_dashboard_is_rtl_language($lang)) {
							$content = wp_native_dashboard_rtl_extension_file_content();
							$response_php = wp_remote_get("http://svn.automattic.com/wordpress-i18n/".$lang."/tags/".$tagged."/dist/wp-content/languages/".$lang.'.php');
							if(!is_wp_error($response_php)) { $content = $response_php['body']; }
							$wp_filesystem->put_contents(WP_CONTENT_DIR.'/languages/'.$lang.'.php', $content, FS_CHMOD_FILE);
						}
						ob_end_clean();
						$can_write_direct = (get_filesystem_method(array()) == 'direct');
						$mo = str_replace('\\', '/', WP_CONTENT_DIR.'/languages/'.$file);
						?>
						<tr id="tr-i18n-installed-<?php echo $lang; ?>">
							<td><span class="i18n-file csp-<?php echo $lang; ?>"><?php echo $lang; ?></span></td>
							<td><?php echo (wp_native_dashboard_is_rtl_language($lang) ? __('right to left', 'wp-native-dashboard') : '&nbsp;'); ?></td>
							<td><?php echo filesize($mo).'&nbsp;Bytes'; ?></td>
							<td><?php if($lang != 'en_US' && $can_write_direct) : ?><a class="csp-delete-local-file" href="<?php echo $mo; ?>"><?php _e('Delete','wp-native-dashboard'); ?></a><?php endif; ?></td>
						</tr>
						<?php
						exit();
					}
				}
				ob_end_clean();
			}
		}
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("The download is currently not available.", 'wp-native-dashboard');
		exit();
	}
	
	
	function on_print_metabox_automattic_i18n() {
		$installed = wp_native_dashboard_collect_installed_languages();
		
		$revision 	= 0;
		$langs 		= $installed;
		$url 		= 'http://svn.automattic.com/wordpress-i18n/';
		$response = wp_remote_get($url);
		$error = is_wp_error($response);
		if(!$error) {
			$lines = split("\n",$response['body']);
			foreach($lines as $line) {
				if (preg_match('/Revision\s*(\d+)/', $line, $hits)) {
					$revision = (int)$hits[1]; 
				}elseif (preg_match("/href\s*=\s*\"(\S+)\/\"/", $line, $hits)) {
					if (in_array($hits[1], array('tools', 'theme', 'pot', 'http://subversion.tigris.org'))) continue;
					if (preg_match("/@/", $hits[1])) continue;
					if (!in_array($hits[1], $langs)) $langs[] = $hits[1];
				}			
			}
			sort($langs);
		}
		?>
		<p><?php echo sprintf(__('All listed languages <em><small>(rev. %d)</small></em> should be supported by polyglot translation teams as download into your WordPress installation.','wp-native-dashboard'), $revision); ?></p>
		<?php if ($error) : ?>
		<p class="center error"><?php _e('The network connection to <strong>svn.automattic.com</strong> is currently not available. Please try again later.', 'wp-native-dashboard'); ?></p>
		<?php else: ?>
		<p class="csp-read-more center"><?php _e('Available for download:', 'wp-native-dashboard'); ?></p>
		<table id="table_svn_i18n" class="widefat fixed" cellspacing="0">
			<tbody>
				<?php
				$state=0;
				foreach($langs as $lang) {
					$state = ($state + 1) % 2;
					$mo = WP_CONTENT_DIR.'/languages/'.$lang.'.mo';
					?>
					<tr id="tr-i18n-svn-download-<?php echo $lang; ?>" class="<?php if ($state) echo 'alternate'; ?><?php if (in_array($lang, $installed)) echo " lang-installed"; ?>">
						<td><span class="i18n-file csp-<?php echo $lang; ?>"><?php echo $lang; ?></span></td>
						<td><?php echo (wp_native_dashboard_is_rtl_language($lang) ? __('right to left', 'wp-native-dashboard') : ''); ?></td>
						<td>
							<?php if (in_array($lang, $installed)) :?>
							<?php _e('installed', 'wp-native-dashboard'); ?>
							<?php elseif($lang == 'en_US') : _e('(build in)', 'wp-native-dashboard'); else : ?>
							<a href="http://svn.automattic.com/wordpress-i18n/<?php echo $lang; ?>/tags/<?php echo $this->tagged_version; ?>/messages/<?php echo $lang; ?>.mo"><?php _e('download', 'wp-native-dashboard'); ?></a>&nbsp;<span><img src="images/loading.gif" class="ajax-feedback" title="" alt="" /></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php endif;	
	}
	
}

?>