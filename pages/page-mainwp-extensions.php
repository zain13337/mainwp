<?php
/**
 * MainWP Extension Page
 *
 * @package     MainWP/Dashboard
 */

namespace MainWP\Dashboard;

/**
 * Class MainWP_Extensions
 */
class MainWP_Extensions {

	/**
	 * Method get_class_name()
	 *
	 * Get Class Name.
	 *
	 * @return object Class name.
	 */
	public static function get_class_name() {
		return __CLASS__;
	}

	/**
	 * Instantiate action hooks.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::get_class_name()
	 */
	public static function init() {
		/**
		 * This hook allows you to render the Extensions page header via the 'mainwp-pageheader-extensions' action.
		 *
		 * @link http://codex.mainwp.com/#mainwp-pageheader-extensions
		 *
		 * @see \MainWP_Extensions::render_header
		 */
		add_action( 'mainwp-pageheader-extensions', array( self::get_class_name(), 'render_header' ) ); // @deprecated Use 'mainwp_pageheader_extensions' instead.
		add_action( 'mainwp_pageheader_extensions', array( self::get_class_name(), 'render_header' ) );

		/**
		 * This hook allows you to render the Extensions page footer via the 'mainwp-pagefooter-extensions' action.
		 *
		 * @link http://codex.mainwp.com/#mainwp-pagefooter-extensions
		 *
		 * @see \MainWP_Extensions::render_footer
		 */
		add_action( 'mainwp-pagefooter-extensions', array( self::get_class_name(), 'render_footer' ) ); // @deprecated Use 'mainwp_pagefooter_extensions' instead.
		add_action( 'mainwp_pagefooter_extensions', array( self::get_class_name(), 'render_footer' ) );

		add_action( 'mainwp_help_sidebar_content', array( self::get_class_name(), 'mainwp_help_content' ) );

		add_filter( 'mainwp-extensions-apigeneratepassword', array( MainWP_Extensions_Handler::get_class_name(), 'gen_api_password' ), 10, 3 );
		add_filter( 'mainwp_extensions_apigeneratepassword', array( MainWP_Extensions_Handler::get_class_name(), 'gen_api_password' ), 10, 3 );
	}

	/**
	 * Method init_menu()
	 *
	 * Instantiate Extensions Menu.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Api_Manager::get_activation_info()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_View::init_menu()
	 * @uses \MainWP\Dashboard\MainWP_Menu::is_disable_menu_item()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::polish_ext_name()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::added_on_menu()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::get_extensions()
	 * @uses  \MainWP\Dashboard\MainWP_Utility::update_option()
	 */
	public static function init_menu() { // phpcs:ignore -- complex function. Current complexity is the only way to achieve desired results, pull request solutions appreciated.
		if ( ! MainWP_Menu::is_disable_menu_item( 2, 'Extensions' ) ) {
			MainWP_Extensions_View::init_menu();
		}

		$save_extensions = array();

		/**
		 * Get extensions
		 *
		 * Adds extension to MainWP system.
		 *
		 * @since 4.1
		 */
		$init_extensions = array();
		$init_extensions = apply_filters_deprecated( 'mainwp-getextensions', array( $init_extensions ), '4.0.7.2', 'mainwp_getextensions' );
		$init_extensions = apply_filters( 'mainwp_getextensions', $init_extensions );

		$all_available_extensions = MainWP_Extensions_View::get_available_extensions( 'all' );

		$activations_cached = get_option( 'mainwp_extensions_all_activation_cached', array() );

		if ( ! is_array( $activations_cached ) ) {
			$activations_cached = array();
		}

		$is_cached = ! empty( $activations_cached ) ? true : false;

		$extraHeaders = array(
			'IconURI'          => 'Icon URI',
			'SupportForumURI'  => 'Support Forum URI',
			'DocumentationURI' => 'Documentation URI',
		);

		$extsPages = array();

		$compatible_v4_checks = array(
			'advanced-uptime-monitor-extension/advanced-uptime-monitor-extension.php',
			'mainwp-article-uploader-extension/mainwp-article-uploader-extension.php',
			'mainwp-backwpup-extension/mainwp-backwpup-extension.php',
			'boilerplate-extension/boilerplate-extension.php',
			'mainwp-branding-extension/mainwp-branding-extension.php',
			'mainwp-bulk-settings-manager/mainwp-bulk-settings-manager.php',
			'mainwp-clean-and-lock-extension/mainwp-clean-and-lock-extension.php',
			'mainwp-client-reports-extension/mainwp-client-reports-extension.php',
			'mainwp-clone-extension/mainwp-clone-extension.php',
			'mainwp-code-snippets-extension/mainwp-code-snippets-extension.php',
			'mainwp-comments-extension/mainwp-comments-extension.php',
			'mainwp-favorites-extension/mainwp-favorites-extension.php',
			'mainwp-file-uploader-extension/mainwp-file-uploader-extension.php',
			'mainwp-google-analytics-extension/mainwp-google-analytics-extension.php',
			'mainwp-maintenance-extension/mainwp-maintenance-extension.php',
			'mainwp-piwik-extension/mainwp-piwik-extension.php',
			'mainwp-post-dripper-extension/mainwp-post-dripper-extension.php',
			'mainwp-rocket-extension/mainwp-rocket-extension.php',
			'mainwp-sucuri-extension/mainwp-sucuri-extension.php',
			'mainwp-team-control/mainwp-team-control.php',
			'mainwp-updraftplus-extension/mainwp-updraftplus-extension.php',
			'mainwp-url-extractor-extension/mainwp-url-extractor-extension.php',
			'mainwp-woocommerce-shortcuts-extension/mainwp-woocommerce-shortcuts-extension.php',
			'mainwp-woocommerce-status-extension/mainwp-woocommerce-status-extension.php',
			'mainwp-wordfence-extension/mainwp-wordfence-extension.php',
			'wordpress-seo-extension/wordpress-seo-extension.php',
			'mainwp-page-speed-extension/mainwp-page-speed-extension.php',
			'mainwp-ithemes-security-extension/mainwp-ithemes-security-extension.php',
			'mainwp-post-plus-extension/mainwp-post-plus-extension.php',
			'mainwp-staging-extension/mainwp-staging-extension.php',
			'mainwp-custom-post-types/mainwp-custom-post-types.php',
			'mainwp-buddy-extension/mainwp-buddy-extension.php',
			'mainwp-vulnerability-checker-extension/mainwp-vulnerability-checker-extension.php',
			'mainwp-timecapsule-extension/mainwp-timecapsule-extension.php',
			'activity-log-mainwp/activity-log-mainwp.php',
		);
		include_once ABSPATH . '/wp-admin/includes/plugin.php';

		$deactivated_imcompatible = array();
		foreach ( $init_extensions as $extension ) {
			$slug        = plugin_basename( $extension['plugin'] );
			$plugin_data = get_plugin_data( $extension['plugin'] );
			$file_data   = get_file_data( $extension['plugin'], $extraHeaders );

			if ( ! isset( $plugin_data['Name'] ) || ( '' === $plugin_data['Name'] ) ) {
				continue;
			}

			if ( in_array( $slug, $compatible_v4_checks ) ) {
				$check_minver = '3.99999';
				if ( 'advanced-uptime-monitor-extension/advanced-uptime-monitor-extension.php' === $slug ) {
					$check_minver = '4.6.2';
				} elseif ( 'activity-log-mainwp/activity-log-mainwp.php' === $slug ) {
					$check_minver = '1.0.5';
				}

				if ( isset( $plugin_data['Version'] ) && version_compare( $plugin_data['Version'], $check_minver, '<' ) ) {
					$deactivated_imcompatible[] = $plugin_data['Name'];
					deactivate_plugins( $slug, true );
					continue;
				}
			}

			$extension['slug'] = $slug;

			if ( ! isset( $extension['name'] ) ) {
				$extension['name'] = $plugin_data['Name'];
			}
			$extension['version']          = $plugin_data['Version'];
			$extension['description']      = $plugin_data['Description'];
			$extension['author']           = $plugin_data['Author'];
			$extension['icon']             = isset( $extension['icon'] ) ? trim( $extension['icon'] ) : '';
			$extension['iconURI']          = $file_data['IconURI'];
			$extension['SupportForumURI']  = $file_data['SupportForumURI'];
			$extension['DocumentationURI'] = $file_data['DocumentationURI'];
			$extension['page']             = 'Extensions-' . str_replace( ' ', '-', ucwords( str_replace( '-', ' ', dirname( $slug ) ) ) );

			if ( isset( $extension['apiManager'] ) && $extension['apiManager'] ) {

				$api_slug = dirname( $slug );

				if ( $is_cached ) {
					$options = isset( $activations_cached[ $api_slug ] ) ? $activations_cached[ $api_slug ] : array();
				} else {
					$options                         = MainWP_Api_Manager::instance()->get_activation_info( $api_slug );
					$activations_cached[ $api_slug ] = $options;
				}

				if ( ! is_array( $options ) ) {
					$options = array();
				}

				$extension['api_key']             = isset( $options['api_key'] ) ? $options['api_key'] : '';
				$extension['activated_key']       = isset( $options['activated_key'] ) ? $options['activated_key'] : 'Deactivated';
				$extension['deactivate_checkbox'] = isset( $options['deactivate_checkbox'] ) ? $options['deactivate_checkbox'] : 'off';
				$extension['product_id']          = isset( $options['product_id'] ) ? $options['product_id'] : '';
				$extension['instance_id']         = isset( $options['instance_id'] ) ? $options['instance_id'] : '';
				$extension['software_version']    = isset( $options['software_version'] ) ? $options['software_version'] : '';
				if ( isset( $options['product_item_id'] ) ) {
					$extension['product_item_id'] = $options['product_item_id'];
				}
			}

			$ext_slug = dirname( $slug );
			if ( isset( $all_available_extensions[ $ext_slug ] ) ) {
				$extension['type'] = $all_available_extensions[ $ext_slug ]['type']; // to fix.
			}

			$save_extensions[] = $extension;
			if ( mainwp_current_user_have_right( 'extension', dirname( $slug ) ) ) {
				if ( isset( $extension['callback'] ) ) {

					$menu_name = MainWP_Extensions_Handler::polish_ext_name( $extension );

					if ( MainWP_Extensions_Handler::added_on_menu( $slug ) ) {
						$_page = add_submenu_page( 'mainwp_tab', $extension['name'], $menu_name, 'read', $extension['page'], $extension['callback'] );
					} else {
						$_page = add_submenu_page( 'mainwp_tab', $extension['name'], '<div class="mainwp-hidden">' . $extension['name'] . '</div>', 'read', $extension['page'], $extension['callback'] );
					}

					if ( isset( $extension['on_load_callback'] ) && ! empty( $extension['on_load_callback'] ) ) {
						add_action( 'load-' . $_page, $extension['on_load_callback'] );
					}

					$extsPages[] = array(
						'title' => $menu_name,
						'slug'  => $extension['page'],
					);
				}
			}
		}

		if ( ! empty( $deactivated_imcompatible ) ) {
			set_transient( 'mainwp_transient_deactivated_incomtible_exts', $deactivated_imcompatible );
		}

		MainWP_Utility::update_option( 'mainwp_extensions', $save_extensions );
		MainWP_Extensions_Handler::get_extensions( true ); // forced reload.

		if ( ! $is_cached ) {
			update_option( 'mainwp_extensions_all_activation_cached', $activations_cached );
		}
		self::init_left_menu( $extsPages );
	}

	/**
	 * Method init_left_menu()
	 *
	 * Initiate top level Extensions Menues.
	 *
	 * @param array $extPages List of extension pages.
	 * @uses \MainWP\Dashboard\MainWP_Menu::is_disable_menu_item()
	 * @uses \MainWP\Dashboard\MainWP_Menu::add_left_menu()
	 * @uses \MainWP\Dashboard\MainWP_Menu::init_subpages_left_menu()
	 */
	public static function init_left_menu( $extPages ) {
		$subPages = array();
		if ( ! MainWP_Menu::is_disable_menu_item( 2, 'Extensions' ) ) {
			MainWP_Menu::add_left_menu(
				array(
					'title'      => __( 'Extensions', 'mainwp' ),
					'parent_key' => 'mainwp_tab',
					'slug'       => 'Extensions',
					'href'       => 'admin.php?page=Extensions',
					'icon'       => '<i class="plug icon"></i>',
					'id'         => 'menu-item-extensions',
				),
				1
			);

			$init_sub_subleftmenu = array(
				array(
					'title'      => __( 'Manage Extensions', 'mainwp' ),
					'parent_key' => 'Extensions',
					'href'       => 'admin.php?page=Extensions',
					'slug'       => 'Extensions',
					'right'      => '',
				),
			);

			MainWP_Menu::init_subpages_left_menu( $subPages, $init_sub_subleftmenu, 'Extensions', 'Extensions' );

			foreach ( $init_sub_subleftmenu as $item ) {
				if ( MainWP_Menu::is_disable_menu_item( 3, $item['slug'] ) ) {
					continue;
				}
				MainWP_Menu::add_left_menu( $item, 2 );
			}

			if ( 0 < count( $extPages ) ) {

				$init_sub_subleftmenu = array();
				$slug                 = '';
				MainWP_Menu::init_subpages_left_menu( $extPages, $init_sub_subleftmenu, 'Extensions', $slug );

				foreach ( $init_sub_subleftmenu as $item ) {
					if ( MainWP_Menu::is_disable_menu_item( 3, $item['slug'] ) ) {
							continue;
					}
					MainWP_Menu::add_left_menu( $item, 2 );
				}
			}
		}
	}

	/**
	 * Method init_subpages_menu()
	 *
	 * Initiate Extensions Subpage Menu.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::get_extensions()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::added_on_menu()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::polish_ext_name()
	 */
	public static function init_subpages_menu() {
		$exts = MainWP_Extensions_Handler::get_extensions();
		if ( empty( $exts ) ) {
			return;
		}
		$html = '';
		foreach ( $exts as $extension ) {
			if ( defined( 'MWP_TEAMCONTROL_PLUGIN_SLUG' ) && ( MWP_TEAMCONTROL_PLUGIN_SLUG == $extension['slug'] ) && ! mainwp_current_user_have_right( 'extension', dirname( MWP_TEAMCONTROL_PLUGIN_SLUG ) ) ) {
				continue;
			}
			if ( MainWP_Extensions_Handler::added_on_menu( $extension['slug'] ) ) {
				continue;
			}

			$menu_name = MainWP_Extensions_Handler::polish_ext_name( $extension );

			if ( isset( $extension['direct_page'] ) ) {
				$html .= '<a href="' . admin_url( 'admin.php?page=' . $extension['direct_page'] ) . '" class="mainwp-submenu">' . $menu_name . '</a>';
			} else {
				$html .= '<a href="' . admin_url( 'admin.php?page=' . $extension['page'] ) . '" class="mainwp-submenu">' . $menu_name . '</a>';
			}
		}
		if ( empty( $html ) ) {
			return;
		}
		?>
		<div id="menu-mainwp-Extensions" class="mainwp-submenu-wrapper" xmlns="http://www.w3.org/1999/html">
			<div class="wp-submenu sub-open" style="">
				<div class="mainwp_boxout mainwp-submenu-wide">
					<div class="mainwp_boxoutin"></div>
					<?php echo $html; ?>
				</div>
			</div>
		</div>
		<?php
	}


	/**
	 * Method ajax_get_purchased_extensions()
	 *
	 * Get purchased MainWP Extensions.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Api_Manager::get_purchased_extension()
	 * @uses \MainWP\Dashboard\MainWP_Api_Manager::check_response_for_intall_errors()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_View::get_available_extensions()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_View::get_extension_groups()
	 * @uses \MainWP\Dashboard\MainWP_Post_Handler::secure_request()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_Handler::get_extensions()
	 * @uses  \MainWP\Dashboard\MainWP_Utility::update_option()
	 */
	public static function ajax_get_purchased_extensions() { // phpcs:ignore -- complex function. Current complexity is the only way to achieve desired results, pull request solutions appreciated.

		MainWP_Post_Handler::instance()->secure_request( 'mainwp_extension_getpurchased' );

		$api_key = isset( $_POST['api_key'] ) ? trim( $_POST['api_key'] ) : false;

		$all_available_extensions_compatible_api_response = array();
		$all_free_pro_exts                                = array();
		$all_org_exts                                     = array();
		$map_extensions_group                             = array();
		$purchased_data                                   = array();

		$all_groups = MainWP_Extensions_View::get_extension_groups();

		$grouped_exts = array( 'others' => '' );

		foreach ( MainWP_Extensions_View::get_available_extensions( 'all' ) as $ext ) {
			$all_available_extensions_compatible_api_response[ $ext['product_id'] ] = $ext;
			$map_extensions_group[ $ext['product_id'] ]                             = current( $ext['group'] );
			if ( 'free' === $ext['type'] || 'pro' === $ext['type'] ) {
				$all_free_pro_exts[ $ext['product_id'] ] = $ext['product_id'];
			} elseif ( 'org' === $ext['type'] ) {
				$all_org_exts[ $ext['product_id'] ] = $ext['product_id'];
			}
		}

		$extensions          = MainWP_Extensions_Handler::get_extensions();
		$installed_softwares = array();

		foreach ( $extensions as $extension ) {
			if ( isset( $extension['type'] ) ) {
				if ( 'free' === $extension['type'] || 'pro' === $extension['type'] ) {
					if ( isset( $extension['product_id'] ) && ! empty( $extension['product_id'] ) ) {
						$installed_softwares[ $extension['product_id'] ] = $extension['product_id'];
					}
				} elseif ( 'org' === $extension['type'] ) {
					$ext_slug                         = dirname( $extension['slug'] );
					$installed_softwares[ $ext_slug ] = $ext_slug;
				}
			}
		}

		$all_disabled_extensions = MainWP_Extensions_Handler::get_extensions_disabled( true );

		foreach ( $all_disabled_extensions as $ext ) {
			$installed_softwares[ $ext['product_id'] ] = $ext['product_id'];
		}

		$not_purchased_exts     = $all_free_pro_exts;
		$not_installed_org_exts = array_diff_key( $all_org_exts, $installed_softwares );
		$installing_exts        = $not_installed_org_exts;

		if ( ! empty( $api_key ) ) {

			$data = MainWP_Api_Manager::instance()->get_purchased_extension( $api_key );

			$result = json_decode( $data, true );
			$return = array();

			if ( is_array( $result ) ) {
				if ( isset( $result['success'] ) && $result['success'] ) {
					$purchased_data     = ( isset( $result['purchased_data'] ) && is_array( $result['purchased_data'] ) ) ? $result['purchased_data'] : array();
					$not_purchased_exts = array_diff_key( $all_free_pro_exts, $purchased_data );
					$installing_exts    = array_diff_key( $purchased_data, $installed_softwares );
					$installing_exts    = array_merge( $installing_exts, $not_installed_org_exts );
				} elseif ( isset( $result['error'] ) ) {
					$return = array( 'error' => $result['error'] );
				}
			} else {
				$apisslverify = get_option( 'mainwp_api_sslVerifyCertificate' );
				if ( 1 == $apisslverify ) {
					MainWP_Utility::update_option( 'mainwp_api_sslVerifyCertificate', 0 );
					$return['retry_action'] = 1;
				}
			}

			if ( ! empty( $return ) ) {
				wp_send_json( $return );
			}
		}

		foreach ( $all_available_extensions_compatible_api_response as $product_id => $ext ) {

			$item_html        = '';
			$error            = '';
			$ext_source_label = '';
			$type             = '';
			$notice           = '';

			$item_slug = MainWP_Utility::get_dir_slug( $ext['slug'] );

			$privacy = '<a href="#" class="extension-privacy-info-link" base-slug="' . esc_attr( $item_slug ) . '" style="text-decoration:none"> <i class="shield alternate small icon"></i></a>';

			$software_title = MainWP_Extensions_Handler::polish_string_name( $ext['title'] );

			if ( ! empty( $ext['type'] ) ) {
				$type = $ext['type'];
				if ( 'free' == $type ) {
					$ext_source_label = '<span class="ui mini green label">FREE</span>';
				} elseif ( 'pro' == $type ) {
					$ext_source_label = '<span class="ui mini blue label">PRO</span>';
				} elseif ( 'org' == $type ) {
					$ext_source_label = '<span class="ui mini grey label">.ORG</span>';
				}
			}

			if ( 'MainWP WordPress SEO Extension' == $product_id || 'wp-seopress-mainwp' == $product_id ) {
				$notice = ' <i class="info circle small icon"></i>';
			}

			$ext_source_label .= '&nbsp;';

			if ( isset( $installing_exts[ $product_id ] ) ) {

				$product_info = isset( $installing_exts[ $product_id ] ) ? $installing_exts[ $product_id ] : array();
				if ( ! is_array( $product_info ) ) {
					$product_info = array();
				}

				if ( 'free' === $type || 'pro' === $type ) {

					if ( $product_info && isset( $product_info['package'] ) && ! empty( $product_info['package'] ) ) {

						/**
						 * API Manager Upgrade URL
						 *
						 * Filters the Upgrade URL for extensions.
						 *
						 * @since Unknown
						 * @ignore
						 */
						$package_url = apply_filters( 'mainwp_api_manager_upgrade_package_url', $product_info['package'], $product_info );

						$item_html = '
									<div class="item extension extension-to-install" download-link="' . esc_url( $package_url ) . '" plugin-slug="" product-id="' . esc_attr( $product_id ) . '" slug="' . esc_attr( $ext['slug'] ) . '">
										<div class="ui stackable grid">
											<div class="two column row">
												<div class="column"><span class="ui checkbox"><input type="checkbox" status="queue"><label>' . $ext_source_label . '<strong><a href="' . esc_url( $ext['link'] ) . '" target="_blank">' . esc_html( $software_title ) . '</a>' . $privacy . ' ' . $notice . '</strong></label></span></div>
												<div class="right aligned column"><span class="installing-extension" status="queue"></span></div>
											</div>
										</div>
									</div>';

					} elseif ( isset( $product_info['error'] ) && ! empty( $product_info['error'] ) ) {
						$error = MainWP_Api_Manager::instance()->check_response_for_intall_errors( $product_info, $software_title );
					} else {
						$error = __( 'Undefined error occurred. Please try again.', 'mainwp' );
					}

					if ( ! empty( $error ) ) {
						$item_html = '
									<div class="item extension">
										<div class="ui stackable grid">
											<div class="two column row">
												<div class="column"><span class="ui checkbox"><input type="checkbox" disabled="disabled"><label>' . $ext_source_label . ' <a href="' . esc_url( $ext['link'] ) . '" target="_blank">' . esc_html( $software_title ) . '</a>' . $privacy . ' ' . $notice . '</label></span></div>
												<div class="right aligned column"><span data-tooltip="' . $error . '" data-inverted="" data-position="left center"><i class="times red icon"></i></span></div>
											</div>
										</div>
									</div>';
					}
				} elseif ( 'org' === $type ) {
					$item_html = '
								<div class="item extension extension-to-install" download-link="" plugin-slug="' . esc_attr( $ext['slug'] ) . '" product-id="' . esc_attr( $product_id ) . '" slug="' . esc_attr( $ext['slug'] ) . '">
									<div class="ui stackable grid">
										<div class="two column row">
											<div class="column"><span class="ui checkbox"><input type="checkbox" status="queue"><label>' . $ext_source_label . '<strong><a href="' . esc_url( $ext['link'] ) . '" target="_blank">' . esc_html( $software_title ) . '</a>' . $privacy . ' ' . $notice . '</strong></label></span></div>
											<div class="right aligned column"><span class="installing-extension" status="queue"></span></div>
										</div>
									</div>
								</div>';
				}
			} elseif ( isset( $not_purchased_exts[ $product_id ] ) ) {
				if ( 'free' === $type || 'pro' === $type ) {
					$item_html = '
						<div class="item extension" product-id="' . $product_id . '" slug="' . esc_attr( $ext['slug'] ) . '">
							<div class="ui stackable grid">
								<div class="two column row">
									<div class="column"><span class="ui checkbox"><input type="checkbox" disabled="disabled"><label>' . $ext_source_label . ' <a href="' . esc_url( $ext['link'] ) . '" target="_blank">' . esc_html( $software_title ) . '</a>' . $privacy . ' ' . $notice . '</label></span></div>
									<div class="right aligned column"><a href="' . $ext['link'] . '" target="_blank" data-tooltip="' . __( 'Extension not purchased. Click to find out more.', 'mainwp' ) . '" data-position="left center" data-inverted=""><i class="info blue icon"></i></a></div>
								</div>
							</div>
						</div>';
				} elseif ( 'org' === $type ) {
					$item_html = '
							<div class="item extension" product-id="' . $product_id . '" slug="' . esc_attr( $ext['slug'] ) . '">
								<div class="ui stackable grid">
									<div class="two column row">
										<div class="column"><span class="ui checkbox"><input type="checkbox" disabled="disabled"><label>' . $ext_source_label . ' <a href="' . esc_url( $ext['link'] ) . '" target="_blank">' . esc_html( $software_title ) . '</a>' . $privacy . ' ' . $notice . '</label></span></div>
										<div class="right aligned column"><a href="' . $ext['url'] . '" target="_blank" data-tooltip="' . __( 'Extension not installed. Click to find out more.', 'mainwp' ) . '" data-position="left center" data-inverted=""><i class="info blue icon"></i></a></div>
									</div>
								</div>
							</div>';
				}
			} elseif ( isset( $installed_softwares[ $product_id ] ) ) {
				$item_html = '
					<div class="item extension" slug="' . esc_attr( $ext['slug'] ) . '">
						<div class="ui stackable grid">
							<div class="two column row">
								<div class="column"><span class="ui checkbox"><input type="checkbox" disabled="disabled"><label>' . $ext_source_label . ' <a href="' . esc_url( $ext['link'] ) . '" target="_blank">' . esc_html( $software_title ) . '</a> ' . $notice . '</label></span>' . $privacy . '</div>
								<div class="right aligned column">Installed</div>
							</div>
						</div>
					</div>';
			}

			$group_id = isset( $map_extensions_group[ $product_id ] ) ? $map_extensions_group[ $product_id ] : false;
			if ( ! empty( $group_id ) && isset( $all_groups[ $group_id ] ) ) {
				if ( isset( $grouped_exts[ $group_id ] ) ) {
					$grouped_exts[ $group_id ] .= $item_html;
				} else {
					$grouped_exts[ $group_id ] = $item_html;
				}
			} else {
				$grouped_exts['others'] .= $item_html;
			}
		}

		$html = '<div class="mainwp-installing-extensions">';

		if ( empty( $installing_exts ) && count( $purchased_data ) == count( $all_free_pro_exts ) ) {
			$html .= '<div class="ui message yellow">' . __( 'All purchased extensions already installed.', 'mainwp' ) . '</div>';
		} else {
			if ( isset( $not_purchased_exts ) && ! empty( $not_purchased_exts ) ) {
				$html .= '<div class="ui message info">' . __( 'You have access to all our Free and third-party Extensions on WP.org and any that you have registered for, but you DO NOT need to install them. ', 'mainwp' );
				$html .= '<br /><br />';
				$html .= __( 'To avoid information overload, we highly recommend adding Extensions one at a time and as you need them. Skip any Extension you do not want to install at this time.', 'mainwp' );
				$html .= '<br /><br />';
				$html .= sprintf( __( 'After installing all your selected Extensions, close the modal by clicking the Close button and %1$sactivate Extensions API license%2$s.', 'mainwp' ), '<a href="https://kb.mainwp.com/docs/activate-extensions-api/" target="_blank">', '</a>' ) . '</div>';
			} else {
				$html .= '<div class="ui message info">' . __( 'You have access to the MainWP Pro plan, which gives you access to all MainWP-created Extensions, but you DO NOT need to install all of them.', 'mainwp' );
				$html .= '<br /><br />';
				$html .= __( 'To avoid information overload, we highly recommend adding Extensions one at a time and as you need them. Skip any Extension you do not want to install at this time.', 'mainwp' );
				$html .= '<br /><br />';
				$html .= sprintf( __( 'After installing all your selected Extensions, close the modal by clicking the Close button and %1$sactivate Extensions API license%2$s.', 'mainwp' ), '<a href="https://kb.mainwp.com/docs/activate-extensions-api/" target="_blank">', '</a>' ) . '</div>';
			}

			$html .= '<div id="mainwp-bulk-activating-extensions-status" class="ui message" style="display:none;"></div>';

			$html .= '<div class="ui secondary green pointing tabular stackable menu" id="mainwp-install-extensions-menu">';
			foreach ( $all_groups as $gr_id => $gr_name ) {
				if ( isset( $grouped_exts[ $gr_id ] ) ) {
					$html .= '<a class="item" data-tab="' . $gr_id . '">' . $gr_name . '</a>';
				}
			}
			if ( isset( $grouped_exts['others'] ) && ! empty( $grouped_exts['others'] ) ) {
				$html .= '<a class="item" data-tab="other">' . __( 'Other', 'mainwp' ) . '</h3>';
			}
			$html .= '</div>';

			foreach ( $all_groups as $gr_id => $gr_name ) {
				if ( isset( $grouped_exts[ $gr_id ] ) ) {
					$html .= '<div class="ui tab" data-tab="' . $gr_id . '">';
					$html .= '<div class="ui hidden divider"></div>';
					$html .= '<h3>' . $gr_name . '</h3>';
					$html .= '<div class="ui hidden divider"></div>';
					$html .= '<div class="ui relaxed divided list">';
					$html .= $grouped_exts[ $gr_id ];
					$html .= '</div>';
					$html .= '</div>';
				}
			}
			if ( isset( $grouped_exts['others'] ) && ! empty( $grouped_exts['others'] ) ) {
				$html .= '<div class="ui tab" data-tab="other">';
				$html .= '<h3>Other</h3>';
				$html .= '<div class="ui relaxed divided list">';
				$html .= $grouped_exts['others'];
				$html .= '</div>';
				$html .= '</div>';
			}
		}
		$html .= '<div class="ui hidden divider"></div>';
		$html .= '<div class="ui hidden divider"></div>';
		$html .= '<div class="ui secondary segment">';
		$html .= '<span class="ui mini green label">FREE</span> - ' . __( 'Free extension developed by MainWP', 'mainwp' ) . '<br/>';
		$html .= '<span class="ui mini blue label">PRO</span> - ' . __( 'Premium extension developed by MainWP', 'mainwp' ) . '<br/>';
		$html .= '<span class="ui mini grey label">.ORG</span> - ' . __( 'Free extension developed by 3rd party author, available on WordPress.org', 'mainwp' );
		$html .= '<div class="ui hidden fitted divider"></div>';
		$html .= '<i class="info circle icon"></i> ' . __( 'Extension requires the corresponding plugin on your MainWP Dashboard site too.', 'mainwp' ) . '<br/>';
		$html .= '<i class="shield alternate icon"></i> ' . __( 'Shows the extension privacy info.', 'mainwp' ) . '<br/>';
		$html .= '</div>';

		$html .= '<script>jQuery( "#mainwp-install-extensions-menu .item" ).tab();</script>';

		if ( ! empty( $installing_exts ) ) {
			$html .= '<p>
				<span class="extension_api_loading">
					<i class="ui active inline loader tiny" style="display: none;"></i><span class="status hidden"></span>
				</span>
			</p> ';
		}

		$html  .= '</div>';
		$return = array(
			'result' => 'SUCCESS',
			'data'   => $html,
		);

		wp_send_json( $return );
	}

	/**
	 * Method render_header()
	 *
	 * Render page header.
	 *
	 * @param string $shownPage The page slug shown at this moment.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Deprecated_Hooks::maybe_handle_deprecated_hook()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_View::render_header()
	 */
	public static function render_header( $shownPage = '' ) {
		MainWP_Deprecated_Hooks::maybe_handle_deprecated_hook();
		MainWP_Extensions_View::render_header( $shownPage );
	}

	/**
	 * Method render_footer()
	 *
	 * Render page footer.
	 *
	 * @param string $shownPage The page slug shown at this moment.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Deprecated_Hooks::maybe_handle_deprecated_hook()
	 * @uses \MainWP\Dashboard\MainWP_Extensions_View::render_footer()
	 */
	public static function render_footer( $shownPage ) {
		MainWP_Deprecated_Hooks::maybe_handle_deprecated_hook();
		MainWP_Extensions_View::render_footer( $shownPage );
	}

	/**
	 * Method render()
	 *
	 * Render page content.
	 *
	 * @uses \MainWP\Dashboard\MainWP_Extensions_View::render()
	 * @uses \MainWP\Dashboard\MainWP_UI::render_top_header()
	 */
	public static function render() {

		$params = array(
			'title' => __( 'Extensions', 'mainwp' ),
		);
		MainWP_UI::render_top_header( $params );

		MainWP_Extensions_View::render();
		echo '</div>';
	}

	/**
	 * Method mainwp_help_content()
	 *
	 * Create the MainWP Help Document List for the help component in the sidebar.
	 */
	public static function mainwp_help_content() {
		if ( isset( $_GET['page'] ) && 'Extensions' === $_GET['page'] ) {
			?>
			<p><?php esc_html_e( 'If you need help with your MainWP Extensions, please review following help documents', 'mainwp' ); ?></p>
			<div class="ui relaxed bulleted list">
				<div class="item"><a href="https://kb.mainwp.com/docs/what-are-mainwp-extensions/" target="_blank"><i class="fa fa-book"></i> What are the MainWP Extensions</a></div>
				<div class="item"><a href="https://kb.mainwp.com/docs/order-extensions/" target="_blank"><i class="fa fa-book"></i> Order Extension(s)</a></div>
				<div class="item"><a href="https://kb.mainwp.com/docs/my-downloads-and-api-keys/" target="_blank"><i class="fa fa-book"></i> My Downloads and API Keys</a></div>
				<div class="item"><a href="https://kb.mainwp.com/docs/install-extensions/" target="_blank"><i class="fa fa-book"></i> Install Extension(s)</a></div>
				<div class="item"><a href="https://kb.mainwp.com/docs/activate-extensions-api/" target="_blank"><i class="fa fa-book"></i> Activate Extension(s) API</a></div>
				<div class="item"><a href="https://kb.mainwp.com/docs/updating-extensions/" target="_blank"><i class="fa fa-book"></i> Updating Extension(s)</a></div>
				<div class="item"><a href="https://kb.mainwp.com/docs/remove-extensions/" target="_blank"><i class="fa fa-book"></i> Remove Extension(s)</a></div>
			</div>
			<?php
		}
	}

}
