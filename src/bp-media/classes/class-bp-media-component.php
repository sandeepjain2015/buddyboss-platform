<?php
/**
 * BuddyBoss Media Component Class.
 *
 * @package BuddyBoss\Media\Loader
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Creates Invites component.
 *
 * @since BuddyBoss 1.0.0
 */
class BP_Media_Component extends BP_Component {


	/**
	 * Default media extension.
	 *
	 * @since BuddyBoss 1.0.0
	 * @todo Is this used anywhere? Is this a duplicate of $default_extension?
	 * @var string
	 */
	var $default_component;

	/**
	 * Default media extension.
	 *
	 * @since BuddyBoss 1.0.0
	 * @var string
	 */
	public $default_extension;

	/**
	 * Illegal media names/slugs.
	 *
	 * @since BuddyBoss 1.0.0
	 * @var array
	 */
	public $forbidden_names;

	/**
	 * Start the media component creation process.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function __construct() {
		parent::start(
			'media',
			__( 'Media', 'buddyboss' ),
			buddypress()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 100,
				'search_query_arg' => 'media_search',
			)
		);

	}

	/**
	 * Include Invites component files.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @see BP_Component::includes() for a description of arguments.
	 *
	 * @param array $includes See BP_Component::includes() for a description.
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'cssjs',
			'filters',
			'template',
			'functions',
			'settings',
		);

		parent::includes( $includes );
	}

	/**
	 * Late includes method.
	 *
	 * Only load up certain code when on specific pages.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function late_includes() {
		// Bail if PHPUnit is running.
		if ( defined( 'BP_TESTS_DIR' ) ) {
			return;
		}

		if ( bp_is_media_component() ) {

			// Screens - Directory.
			if ( bp_is_media_directory() ) {
				require $this->path . 'bp-media/screens/directory.php';
			}

			// Screens - User profile integration.
			if ( bp_is_user() ) {
				require $this->path . 'bp-media/screens/media.php';

				/*
				 * Nav items.
				 *
				 * 'album' is not a registered nav item, but we add a screen handler manually.
				 */
				if ( bp_is_user_media() && in_array( bp_current_action(), array( 'albums' ), true ) ) {
					require $this->path . 'bp-media/screens/' . bp_current_action() . '.php';
				}
			}

			// Theme compatibility.
			new BP_Media_Theme_Compat();
		}
	}

	/**
	 * Set up component global data.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @see BP_Component::setup_globals() for a description of arguments.
	 *
	 * @param array $args See BP_Component::setup_globals() for a description.
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Define a slug, if necessary.
		if ( ! defined( 'BP_MEDIA_SLUG' ) ) {
			define( 'BP_MEDIA_SLUG', $this->id );
		}

		// Global tables for media component.
		$global_tables = array(
			'table_name'        => $bp->table_prefix . 'bp_media',
			'table_name_albums' => $bp->table_prefix . 'bp_media_albums',
		);

		// Fetch the default directory title.
		$default_directory_titles = bp_core_get_directory_page_default_titles();
		$default_directory_title  = $default_directory_titles[$this->id];

		// All globals for media component.
		// Note that global_tables is included in this array.
		parent::setup_globals( array(
			'slug'                  => BP_MEDIA_SLUG,
			'root_slug'             => isset( $bp->pages->media->slug ) ? $bp->pages->media->slug : BP_MEDIA_SLUG,
			'has_directory'         => true,
			'notification_callback' => 'bp_media_format_notifications',
			'global_tables'         => $global_tables,
			'directory_title'       => isset( $bp->pages->media->title ) ? $bp->pages->media->title : $default_directory_title,
			'search_string'         => __( 'Search Media&hellip;', 'buddyboss' ),
		) );

	}

	/**
	 * Set up component navigation.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @see BP_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav() for description.
	 * @param array $sub_nav  Optional. See BP_Component::setup_nav() for description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Determine user to use.
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		$access     = bp_core_can_edit_settings();
		$slug       = bp_get_media_slug();
		$media_link = trailingslashit( $user_domain . $slug );

		// Only grab count if we're on a user page and current user has access.
		if ( bp_is_user() && bp_user_has_access() ) {
			$count    = 0;
			$class    = ( 0 === $count ) ? 'no-count' : 'count';
			$nav_name = sprintf(
			/* translators: %s: Unread message count for the current user */
				__( 'Media %s', 'buddyboss' ),
				sprintf(
					'<span class="%s">%s</span>',
					esc_attr( $class ),
					bp_core_number_format( $count )
				)
			);
		} else {
			$nav_name = __( 'Media', 'buddyboss' );
		}

		// Add 'Media' to the main navigation.
		$main_nav = array(
			'name'                => $nav_name,
			'slug'                => $slug,
			'position'            => 80,
			'screen_function'     => 'media_screen',
			'default_subnav_slug' => 'my-media',
			'user_has_access'     => $access,
			'item_css_id'         => $this->id
		);

		// Add the subnav items to the profile.
		$sub_nav[] = array(
			'name'            => $nav_name,
			'slug'            => 'my-media',
			'parent_url'      => $media_link,
			'parent_slug'     => $slug,
			'screen_function' => 'media_screen',
			'position'        => 10,
			'item_css_id'     => 'media-my-media'
		);

		// Add the subnav items to the profile.
		$sub_nav[] = array(
			'name'            => __( 'Albums', 'buddyboss' ),
			'slug'            => 'albums',
			'parent_url'      => $media_link,
			'parent_slug'     => $slug,
			'screen_function' => 'media_screen',
			'position'        => 10,
			'user_has_access' => $access
		);

		parent::setup_nav( $main_nav, $sub_nav );

	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @see BP_Component::setup_nav() for a description of the $wp_admin_nav
	 *      parameter array.
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar() for a description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		// Menus for logged in user.
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables.
			$media_link = trailingslashit( bp_loggedin_user_domain() . bp_get_media_slug() );

			// Add main Messages menu.
			$wp_admin_nav[] = array(
				'parent' => buddypress()->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Media', 'buddyboss' ),
				'href'   => $media_link
			);

			// Media.
			$wp_admin_nav[] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-my-media',
				'title'    => __( 'My Media', 'buddyboss' ),
				'href'     => $media_link,
				'position' => 10
			);

			// Albums.
			$wp_admin_nav[] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-albums',
				'title'    => __( 'My Albums', 'buddyboss' ),
				'href'     => trailingslashit( $media_link . 'albums' ),
				'position' => 20
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Setup cache groups.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function setup_cache_groups() {

		// Global groups.
		wp_cache_add_global_groups( array(
			'bp_media',
			'bp_media_albums'
		) );

		parent::setup_cache_groups();
	}
}