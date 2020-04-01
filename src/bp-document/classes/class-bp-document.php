<?php
/**
 * BuddyBoss Document Classes
 *
 * @package BuddyBoss\Document
 * @since BuddyBoss 1.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database interaction class for the BuddyBoss document component.
 * Instance methods are available for creating/editing an document,
 * static methods for querying document.
 *
 * @since BuddyBoss 1.3.0
 */
class BP_Document {

	/** Properties ************************************************************/

	/**
	 * ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $id;

	/**
	 * Blog ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $blog_id;

	/**
	 * Attachment ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $attachment_id;

	/**
	 * User ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $user_id;

	/**
	 * Title of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var string
	 */
	var $title;

	/**
	 * Folder ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $folder_id;

	/**
	 * Activity ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $activity_id;

	/**
	 * Group ID of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $group_id;

	/**
	 * Privacy of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var string
	 */
	var $privacy;

	/**
	 * Menu order of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var int
	 */
	var $menu_order;

	/**
	 * Upload date of the document item.
	 *
	 * @since BuddyBoss 1.3.0
	 * @var string
	 */
	var $date_created;

	/**
	 * Error holder.
	 *
	 * @since BuddyBoss 1.3.0
	 *
	 * @var WP_Error
	 */
	public $errors;

	/**
	 * Error type to return. Either 'bool' or 'wp_error'.
	 *
	 * @since BuddyBoss 1.3.0
	 *
	 * @var string
	 */
	public $error_type = 'bool';

	/**
	 * Constructor method.
	 *
	 * @param int|bool $id Optional. The ID of a specific activity item.
	 *
	 * @since BuddyBoss 1.3.0
	 */
	function __construct( $id = false ) {
		// Instantiate errors object.
		$this->errors = new WP_Error();

		if ( ! empty( $id ) ) {
			$this->id = (int) $id;
			$this->populate();
		}
	}

	/**
	 * Populate the object with data about the specific document item.
	 *
	 * @since BuddyBoss 1.3.0
	 */
	public function populate() {

		global $wpdb;

		$row = wp_cache_get( $this->id, 'bp_document' );

		if ( false === $row ) {
			$bp  = buddypress();
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->document->table_name} WHERE id = %d", $this->id ) ); // db call ok; no-cache ok;

			wp_cache_set( $this->id, $row, 'bp_document' );
		}

		if ( empty( $row ) ) {
			$this->id = 0;

			return;
		}

		$this->id            = (int) $row->id;
		$this->blog_id       = (int) $row->blog_id;
		$this->attachment_id = (int) $row->attachment_id;
		$this->user_id       = (int) $row->user_id;
		$this->title         = $row->title;
		$this->folder_id     = (int) $row->album_id;
		$this->activity_id   = (int) $row->activity_id;
		$this->group_id      = (int) $row->group_id;
		$this->privacy       = $row->privacy;
		$this->menu_order    = (int) $row->menu_order;
		$this->date_created  = $row->date_created;
	}

	/**
	 * Save the document item to the database.
	 *
	 * @return WP_Error|bool True on success.
	 * @since BuddyBoss 1.3.0
	 */
	public function save() {

		global $wpdb;

		$bp = buddypress();

		$this->id            = apply_filters_ref_array( 'bp_document_id_before_save', array( $this->id, &$this ) );
		$this->blog_id       = apply_filters_ref_array( 'bp_document_blog_id_before_save', array( $this->blog_id, &$this ) );
		$this->attachment_id = apply_filters_ref_array( 'bp_document_attachment_id_before_save', array( $this->attachment_id, &$this ) );
		$this->user_id       = apply_filters_ref_array( 'bp_document_user_id_before_save', array( $this->user_id, &$this ) );
		$this->title         = apply_filters_ref_array( 'bp_document_title_before_save', array( $this->title, &$this ) );
		$this->type          = apply_filters_ref_array( 'bp_document_type_before_save', array( 'document', &$this ) );
		$this->folder_id     = apply_filters_ref_array( 'bp_document_folder_id_before_save', array( $this->folder_id, &$this ) );
		$this->activity_id   = apply_filters_ref_array( 'bp_document_activity_id_before_save', array( $this->activity_id, &$this ) );
		$this->group_id      = apply_filters_ref_array( 'bp_document_group_id_before_save', array( $this->group_id, &$this ) );
		$this->privacy       = apply_filters_ref_array( 'bp_document_privacy_before_save', array( $this->privacy, &$this ) );
		$this->menu_order    = apply_filters_ref_array( 'bp_document_menu_order_before_save', array( $this->menu_order, &$this ) );
		$this->date_created  = apply_filters_ref_array( 'bp_document_date_created_before_save', array( $this->date_created, &$this ) );

		/**
		 * Fires before the current document item gets saved.
		 *
		 * Please use this hook to filter the properties above. Each part will be passed in.
		 *
		 * @param BP_Document $this Current instance of the document item being saved. Passed by reference.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		do_action_ref_array( 'bp_document_before_save', array( &$this ) );

		if ( 'wp_error' === $this->error_type && $this->errors->get_error_code() ) {
			return $this->errors;
		}

		if ( empty( $this->attachment_id ) // || empty( $this->activity_id ) //todo: when forums document is saving, it should have activity id assigned if settings enabled need to check this
		) {
			if ( 'bool' === $this->error_type ) {
				return false;
			} else {
				if ( empty( $this->activity_id ) ) {
					$this->errors->add( 'bp_document_missing_activity' );
				} else {
					$this->errors->add( 'bp_document_missing_attachment' );
				}

				return $this->errors;
			}
		}

		// Generate PDF file preview image.
		$attachment_id = $this->attachment_id;

		$is_preview_generated  = get_post_meta( $attachment_id, 'document_preview_generated', true );
		$preview_attachment_id = (int) get_post_meta( $attachment_id, 'document_preview_attachment_id', true );
		if ( empty( $is_preview_generated ) ) {
			$extension             = bp_document_extension( $attachment_id );
			$preview_attachment_id = 0;
			$file                  = get_attached_file( $attachment_id );
			$upload_dir            = wp_upload_dir();


			if ( 'pdf' === $extension ) {

				$output_format = 'jpeg';
				$antialiasing  = '4';
				$preview_page  = '1';
				$resolution    = '300';
				$output_file   = $upload_dir['basedir'] . '/' . $attachment_id . '_imagick_preview.jpg';
				$exec_command  = 'gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=' . $output_format . ' ';
				$exec_command .= '-dTextAlphaBits=' . $antialiasing . ' -dGraphicsAlphaBits=' . $antialiasing . ' ';
				$exec_command .= '-dFirstPage=' . $preview_page . ' -dLastPage=' . $preview_page . ' ';
				$exec_command .= '-r' . $resolution . ' ';
				$exec_command .= '-sOutputFile=' . $output_file . " '" . $file . "'";

				exec( $exec_command, $command_output, $return_val );

				if ( file_exists( $output_file ) ) {
					$image_data = file_get_contents( $output_file );

					$filename = basename( $output_file );

					if ( wp_mkdir_p( $upload_dir['path'] ) ) {
						$file = $upload_dir['path'] . '/' . $filename;
					} else {
						$file = $upload_dir['basedir'] . '/' . $filename;
					}

					file_put_contents( $file, $image_data );

					$wp_filetype = wp_check_filetype( $filename, null );

					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => sanitize_file_name( $filename ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);

					$preview_attachment_id = wp_insert_attachment( $attachment, $file );
					require_once ABSPATH . 'wp-admin/includes/image.php';
					$attach_data = wp_generate_attachment_metadata( $preview_attachment_id, $file );
					wp_update_attachment_metadata( $preview_attachment_id, $attach_data );
					update_post_meta( $attachment_id, 'document_preview_generated', 'yes' );
					update_post_meta( $attachment_id, 'document_preview_attachment_id', $preview_attachment_id );
					unlink( $output_file );

				}
			} else {

				$dir = $upload_dir['basedir'] . '/doc' . $attachment_id;
				wp_mkdir_p( $dir );

				$pattern = $dir . '/*.*';
				$command = 'soffice --headless "-env:UserInstallation=file:///tmp/LibreOffice_Conversion_${USER}" --convert-to pdf:writer_pdf_Export --outdir ' . $dir . '/ ' . $file;
				exec( $command, $command_output, $return_val );

				if ( is_array( glob( $pattern ) ) ) {
					foreach ( glob( $pattern ) as $filename ) {

						$output_format = 'jpeg';
						$antialiasing  = '4';
						$preview_page  = '1';
						$resolution    = '300';
						$output_file   = $upload_dir['basedir'] . '/' . $attachment_id . '_imagick_preview.jpg';
						$exec_command  = 'gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=' . $output_format . ' ';
						$exec_command  .= '-dTextAlphaBits=' . $antialiasing . ' -dGraphicsAlphaBits=' . $antialiasing . ' ';
						$exec_command  .= '-dFirstPage=' . $preview_page . ' -dLastPage=' . $preview_page . ' ';
						$exec_command  .= '-r' . $resolution . ' ';
						$exec_command  .= '-sOutputFile=' . $output_file . " '" . $filename . "'";

						exec( $exec_command, $command_output, $return_val );

						if ( file_exists( $output_file ) ) {
							$image_data = file_get_contents( $output_file );

							$filename = basename( $output_file );

							if ( wp_mkdir_p( $upload_dir['path'] ) ) {
								$file = $upload_dir['path'] . '/' . $filename;
							} else {
								$file = $upload_dir['basedir'] . '/' . $filename;
							}

							file_put_contents( $file, $image_data );

							$wp_filetype = wp_check_filetype( $filename, null );

							$attachment = array(
								'post_mime_type' => $wp_filetype['type'],
								'post_title'     => sanitize_file_name( $filename ),
								'post_content'   => '',
								'post_status'    => 'inherit',
							);

							$preview_attachment_id = wp_insert_attachment( $attachment, $file );
							require_once ABSPATH . 'wp-admin/includes/image.php';
							$attach_data = wp_generate_attachment_metadata( $preview_attachment_id, $file );
							wp_update_attachment_metadata( $preview_attachment_id, $attach_data );
							update_post_meta( $attachment_id, 'document_preview_generated', 'yes' );
							update_post_meta( $attachment_id, 'document_preview_attachment_id', $preview_attachment_id );
							unlink( $output_file );

						}
					}
				}
				$this->bp_document_remove_temp_directory( $dir );

			}
		}

		// If we have an existing ID, update the document item, otherwise insert it.
		if ( ! empty( $this->id ) ) {
			$q = $wpdb->prepare( "UPDATE {$bp->document->table_name} SET blog_id = %d, attachment_id = %d, user_id = %d, title = %s, album_id = %d, activity_id = %d, group_id = %d, privacy = %s, menu_order = %d, date_created = %s, type = %s, preview_attachment_id = %d WHERE id = %d", $this->blog_id, $this->attachment_id, $this->user_id, $this->title, $this->folder_id, $this->activity_id, $this->group_id, $this->privacy, $this->menu_order, $this->date_created, 'document', $preview_attachment_id, $this->id );
		} else {
			$q = $wpdb->prepare( "INSERT INTO {$bp->document->table_name} ( blog_id, attachment_id, user_id, title, album_id, activity_id, group_id, privacy, menu_order, date_created, type, preview_attachment_id ) VALUES ( %d, %d, %d, %s, %d, %d, %d, %s, %d, %s, %s, %d )", $this->blog_id, $this->attachment_id, $this->user_id, $this->title, $this->folder_id, $this->activity_id, $this->group_id, $this->privacy, $this->menu_order, $this->date_created, 'document', $preview_attachment_id );
		}

		if ( false === $wpdb->query( $q ) ) {
			return false;
		}

		// If this is a new document item, set the $id property.
		if ( empty( $this->id ) ) {
			$this->id = $wpdb->insert_id;
		}

		/**
		 * Fires after an document item has been saved to the database.
		 *
		 * @param BP_Document $this Current instance of document item being saved. Passed by reference.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		do_action_ref_array( 'bp_document_after_save', array( &$this ) );

		return true;
	}

	public function bp_document_remove_temp_directory( $dir ) {
		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != '.' && $object != '..' ) {
					if ( filetype( $dir . '/' . $object ) == 'dir' ) {
						rrmdir( $dir . '/' . $object );
					} else {
						unlink( $dir . '/' . $object );
					}
				}
			}
			reset( $objects );
			rmdir( $dir );
		}
	}

	/** Static Methods ***************************************************/

	/**
	 * Get document items, as specified by parameters.
	 *
	 * @param array $args {
	 *     An array of arguments. All items are optional.
	 *
	 * @type int $page Which page of results to fetch. Using page=1 without per_page will result
	 *                                           in no pagination. Default: 1.
	 * @type int|bool $per_page Number of results per page. Default: 20.
	 * @type int|bool $max Maximum number of results to return. Default: false (unlimited).
	 * @type string $fields Document fields to return. Pass 'ids' to get only the document IDs.
	 *                                           'all' returns full document objects.
	 * @type string $sort ASC or DESC. Default: 'DESC'.
	 * @type string $order_by Column to order results by.
	 * @type array $exclude Array of document IDs to exclude. Default: false.
	 * @type string $search_terms Limit results by a search term. Default: false.
	 * @type string|bool $count_total If true, an additional DB query is run to count the total document items
	 *                                           for the query. Default: false.
	 * }
	 * @return array The array returned has two keys:
	 *               - 'total' is the count of located documents
	 *               - 'documents' is an array of the located documents
	 * @since BuddyBoss 1.3.0
	 */
	public static function get( $args = array() ) {

		global $wpdb;

		$bp = buddypress();
		$r  = wp_parse_args(
			$args,
			array(
				'scope'          => '',              // Scope - Groups, friends etc.
				'page'           => 1,               // The current page.
				'per_page'       => 20,              // Document items per page.
				'max'            => false,           // Max number of items to return.
				'fields'         => 'all',           // Fields to include.
				'sort'           => 'DESC',          // ASC or DESC.
				'order_by'       => 'date_created',  // Column to order by.
				'exclude'        => false,           // Array of ids to exclude.
				'in'             => false,           // Array of ids to limit query by (IN).
				'search_terms'   => false,           // Terms to search by.
				'privacy'        => false,           // public, loggedin, onlyme, friends, grouponly, message.
				'count_total'    => false,           // Whether or not to use count_total.
				'folder_id'      => false,
				'folder'         => true,
				'user_directory' => true,
			)
		);

		// Select conditions.
		$select_sql = 'SELECT DISTINCT m.id';

		$from_sql = " FROM {$bp->document->table_name} m";

		$join_sql = '';

		// Where conditions.
		$where_conditions = array();

		if ( ! empty( $r['scope'] ) ) {
			$scope_query = self::get_scope_query_sql( $r['scope'], $r );

			// Override some arguments if needed.
			if ( ! empty( $scope_query['override'] ) ) {
				$r = array_replace_recursive( $r, $scope_query['override'] );
			}
		}

		// Searching.
		if ( $r['search_terms'] ) {
			$search_terms_like              = '%' . bp_esc_like( $r['search_terms'] ) . '%';
			$where_conditions['search_sql'] = $wpdb->prepare( 'm.title LIKE %s', $search_terms_like );

			/**
			 * Filters whether or not to include users for search parameters.
			 *
			 * @param bool $value Whether or not to include user search. Default false.
			 *
			 * @since BuddyBoss 1.3.0
			 */
			if ( apply_filters( 'bp_document_get_include_user_search', false ) ) {
				$user_search = get_user_by( 'slug', $r['search_terms'] );
				if ( false !== $user_search ) {
					$user_id                         = $user_search->ID;
					$where_conditions['search_sql'] .= $wpdb->prepare( ' OR m.user_id = %d', $user_id );
				}
			}
		}

		// Sorting.
		$sort = $r['sort'];
		if ( $sort !== 'ASC' && $sort !== 'DESC' ) {
			$sort = 'DESC';
		}

		switch ( $r['order_by'] ) {
			case 'id':
			case 'user_id':
			case 'blog_id':
			case 'attachment_id':
			case 'title':
			case 'folder':
			case 'folder_id':
			case 'activity_id':
			case 'group_id':
			case 'menu_order':
				break;

			default:
				$r['order_by'] = 'date_created';
				break;
		}
		$order_by = 'm.' . $r['order_by'];

		// Exclude specified items.
		if ( ! empty( $r['exclude'] ) ) {
			$exclude                     = implode( ',', wp_parse_id_list( $r['exclude'] ) );
			$where_conditions['exclude'] = "m.id NOT IN ({$exclude})";
		}

		// The specific ids to which you want to limit the query.
		if ( ! empty( $r['in'] ) ) {
			$in                     = implode( ',', wp_parse_id_list( $r['in'] ) );
			$where_conditions['in'] = "m.id IN ({$in})";

			// we want to disable limit query when include document ids
			$r['page']     = false;
			$r['per_page'] = false;
		}

		if ( ! empty( $r['activity_id'] ) ) {
			$where_conditions['activity'] = "m.activity_id = {$r['activity_id']}";
		}

		// existing-document check to query document which has no folders assigned
		if ( ! empty( $r['folder_id'] ) && 'existing-document' != $r['folder_id'] ) {
			$where_conditions['folder'] = "m.album_id = {$r['folder_id']}";
		} elseif ( ! empty( $r['folder_id'] ) && 'existing-document' == $r['folder_id'] ) {
			$where_conditions['folder'] = 'm.album_id = 0';
		}

		if ( ! empty( $r['user_id'] ) ) {
			$where_conditions['user'] = "m.user_id = {$r['user_id']}";
		}

		if ( ! empty( $r['group_id'] ) ) {
			$where_conditions['user'] = "m.group_id = {$r['group_id']}";
		}

		$where_conditions['type'] = "m.type = 'document'";

		if ( ! empty( $r['privacy'] ) ) {
			$privacy                     = "'" . implode( "', '", $r['privacy'] ) . "'";
			$where_conditions['privacy'] = "m.privacy IN ({$privacy})";
		}

		/**
		 * Filters the MySQL WHERE conditions for the Document items get method.
		 *
		 * @param array $where_conditions Current conditions for MySQL WHERE statement.
		 * @param array $r Parsed arguments passed into method.
		 * @param string $select_sql Current SELECT MySQL statement at point of execution.
		 * @param string $from_sql Current FROM MySQL statement at point of execution.
		 * @param string $join_sql Current INNER JOIN MySQL statement at point of execution.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		$where_conditions = apply_filters( 'bp_document_get_where_conditions', $where_conditions, $r, $select_sql, $from_sql, $join_sql );

		if ( empty( $where_conditions ) ) {
			$where_conditions['2'] = '2';
		}

		// Join the where conditions together.
		if ( ! empty( $scope_query['sql'] ) ) {
			$where_sql = 'WHERE ( ' . join( ' AND ', $where_conditions ) . ' ) OR ( ' . $scope_query['sql'] . ' )';
		} else {
			$where_sql = 'WHERE ' . join( ' AND ', $where_conditions );
		}

		/**
		 * Filter the MySQL JOIN clause for the main document query.
		 *
		 * @param string $join_sql JOIN clause.
		 * @param array $r Method parameters.
		 * @param string $select_sql Current SELECT MySQL statement.
		 * @param string $from_sql Current FROM MySQL statement.
		 * @param string $where_sql Current WHERE MySQL statement.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		$join_sql = apply_filters( 'bp_document_get_join_sql', $join_sql, $r, $select_sql, $from_sql, $where_sql );

		// Sanitize page and per_page parameters.
		$page     = absint( $r['page'] );
		$per_page = absint( $r['per_page'] );

		$retval = array(
			'documents'      => null,
			'total'          => null,
			'has_more_items' => null,
		);

		// Query first for document IDs.
		$document_ids_sql = "{$select_sql} {$from_sql} {$join_sql} {$where_sql} ORDER BY {$order_by} {$sort}, m.id {$sort}";

		if ( ! empty( $per_page ) && ! empty( $page ) ) {
			// We query for $per_page + 1 items in order to
			// populate the has_more_items flag.
			$document_ids_sql .= $wpdb->prepare( ' LIMIT %d, %d', absint( ( $page - 1 ) * $per_page ), $per_page + 1 );
		}

		/**
		 * Filters the paged document MySQL statement.
		 *
		 * @param string $document_ids_sql MySQL statement used to query for Document IDs.
		 * @param array $r Array of arguments passed into method.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		$document_ids_sql = apply_filters( 'bp_document_paged_activities_sql', $document_ids_sql, $r );

		$cache_group = 'bp_document';

		$cached = bp_core_get_incremented_cache( $document_ids_sql, $cache_group );
		if ( false === $cached ) {
			$document_ids = $wpdb->get_col( $document_ids_sql ); // db call ok; no-cache ok;
			bp_core_set_incremented_cache( $document_ids_sql, $cache_group, $document_ids );
		} else {
			$document_ids = $cached;
		}

		$retval['has_more_items'] = ! empty( $per_page ) && count( $document_ids ) > $per_page;

		// If we've fetched more than the $per_page value, we
		// can discard the extra now.
		if ( ! empty( $per_page ) && count( $document_ids ) === $per_page + 1 ) {
			array_pop( $document_ids );
		}

		if ( 'ids' === $r['fields'] ) {
			$documents = array_map( 'intval', $document_ids );
		} else {
			$documents = self::get_document_data( $document_ids );
		}

		if ( 'ids' !== $r['fields'] ) {
			// Get the fullnames of users so we don't have to query in the loop.
			// $documents = self::append_user_fullnames( $documents );

			// Pre-fetch data associated with document users and other objects.
			$documents = self::prefetch_object_data( $documents );
		}

		$retval['documents'] = $documents;

		// If $max is set, only return up to the max results.
		if ( ! empty( $r['count_total'] ) ) {

			/**
			 * Filters the total document MySQL statement.
			 *
			 * @param string $value MySQL statement used to query for total documents.
			 * @param string $where_sql MySQL WHERE statement portion.
			 * @param string $sort Sort direction for query.
			 *
			 * @since BuddyBoss 1.3.0
			 */
			$total_documents_sql = apply_filters( 'bp_document_total_documents_sql', "SELECT count(DISTINCT m.id) FROM {$bp->document->table_name} m {$join_sql} {$where_sql}", $where_sql, $sort );
			$cached              = bp_core_get_incremented_cache( $total_documents_sql, $cache_group );
			if ( false === $cached ) {
				$total_documents = $wpdb->get_var( $total_documents_sql ); // db call ok; no-cache ok;
				bp_core_set_incremented_cache( $total_documents_sql, $cache_group, $total_documents );
			} else {
				$total_documents = $cached;
			}

			if ( ! empty( $r['max'] ) ) {
				if ( (int) $total_documents > (int) $r['max'] ) {
					$total_documents = $r['max'];
				}
			}

			$retval['total'] = $total_documents;
		}

		return $retval;
	}

	public static function documents( $args = array() ) {

		global $wpdb;

		$bp = buddypress();
		$r  = wp_parse_args(
			$args,
			array(
				'scope'          => '',              // Scope - Groups, friends etc.
				'page'           => 1,               // The current page.
				'per_page'       => 20,              // Document items per page.
				'max'            => false,           // Max number of items to return.
				'fields'         => 'all',           // Fields to include.
				'sort'           => 'DESC',          // ASC or DESC.
				'order_by'       => 'date_created',  // Column to order by.
				'exclude'        => false,           // Array of ids to exclude.
				'in'             => false,           // Array of ids to limit query by (IN).
				'search_terms'   => false,           // Terms to search by.
				'privacy'        => false,           // public, loggedin, onlyme, friends, grouponly, message.
				'count_total'    => false,           // Whether or not to use count_total.
				'folder'         => true,
				'user_directory' => true,
			)
		);

		// Select conditions.
		$select_sql_document = 'SELECT DISTINCT m.id';
		$select_sql_folder   = 'SELECT DISTINCT a.id';

		$from_sql_document = " FROM {$bp->document->table_name} m";
		$from_sql_folder   = " FROM {$bp->document->table_name_folders} a";

		$join_sql_document = '';
		$join_sql_folder   = '';

		// Where conditions.
		$where_conditions_document = array();
		$where_conditions_folder   = array();

		if ( ! empty( $r['scope'] ) ) {
			$scope_query_document = self::get_scope_document_query_sql( $r['scope'], $r );
			$scope_query_folder   = self::get_scope_folder_query_sql( $r['scope'], $r );

			// Override some arguments if needed.
			if ( ! empty( $scope_query_document['override'] ) ) {
				$r = array_replace_recursive( $r, $scope_query_document['override'] );
			}

			// Override some arguments if needed.
			if ( ! empty( $scope_query_folder['override'] ) ) {
				$r = array_replace_recursive( $r, $scope_query_folder['override'] );
			}
		}

		// Searching.
		if ( $r['search_terms'] ) {
			$search_terms_like                       = '%' . bp_esc_like( $r['search_terms'] ) . '%';
			$where_conditions_document['search_sql'] = $wpdb->prepare( 'm.title LIKE %s', $search_terms_like );
			$where_conditions_folder['search_sql']   = $wpdb->prepare( 'a.title LIKE %s', $search_terms_like );

			/**
			 * Filters whether or not to include users for search parameters.
			 *
			 * @param bool $value Whether or not to include user search. Default false.
			 *
			 * @since BuddyBoss 1.3.0
			 */
			if ( apply_filters( 'bp_document_get_include_user_search', false ) ) {
				$user_search = get_user_by( 'slug', $r['search_terms'] );
				if ( false !== $user_search ) {
					$user_id                                  = $user_search->ID;
					$where_conditions_document['search_sql'] .= $wpdb->prepare( ' OR m.user_id = %d', $user_id );
					$where_conditions_folder['search_sql']   .= $wpdb->prepare( ' OR a.user_id = %d', $user_id );
				}
			}
		}

		// Sorting.
		$sort = $r['sort'];
		if ( $sort !== 'ASC' && $sort !== 'DESC' ) {
			$sort = 'DESC';
		}

		switch ( $r['order_by'] ) {
			case 'id':
			case 'user_id':
			case 'blog_id':
			case 'attachment_id':
			case 'title':
			case 'folder_id':
			case 'activity_id':
			case 'group_id':
			case 'menu_order':
				break;

			default:
				$r['order_by'] = 'date_created';
				break;
		}
		$order_by_document = 'm.' . $r['order_by'];
		$order_by_folder   = 'a.date_created';

		// Exclude specified items.
		if ( ! empty( $r['exclude'] ) ) {
			$exclude                              = implode( ',', wp_parse_id_list( $r['exclude'] ) );
			$where_conditions_document['exclude'] = "m.id NOT IN ({$exclude})";
			$where_conditions_folder['exclude']   = "a.id NOT IN ({$exclude})";
		}

		// The specific ids to which you want to limit the query.
		if ( ! empty( $r['in'] ) ) {
			$in                              = implode( ',', wp_parse_id_list( $r['in'] ) );
			$where_conditions_document['in'] = "m.id IN ({$in})";
			$where_conditions_folder['in']   = "a.id IN ({$in})";
		}

		if ( ! empty( $r['activity_id'] ) ) {
			$where_conditions_document['activity'] = "m.activity_id = {$r['activity_id']}";
		}

		// existing-document check to query document which has no folders assigned
		if ( ! empty( $r['folder_id'] ) && 'existing-document' !== $r['folder_id'] ) {
			$where_conditions_document['folder'] = "m.album_id = {$r['folder_id']}";
		} elseif ( ! empty( $r['folder_id'] ) && 'existing-document' === $r['folder_id'] ) {
			$where_conditions_document['folder'] = 'm.album_id = 0';
		}

		if ( ! empty( $r['user_id'] ) ) {
			$where_conditions_document['user'] = "m.user_id = {$r['user_id']}";
			$where_conditions_folder['user']   = "a.user_id = {$r['user_id']}";
		}

		if ( ! empty( $r['group_id'] ) ) {
			$where_conditions_document['user'] = "m.group_id = {$r['group_id']}";
			$where_conditions_folder['user']   = "a.group_id = {$r['group_id']}";
		}

		if ( ! empty( $r['user_directory'] ) && true === $r['user_directory'] ) {
			if ( ! empty( $r['folder_id'] ) && 'existing-document' !== $r['folder_id'] ) {
				$where_conditions_folder['user_directory'] = "a.parent = {$r['folder_id']}";
			} elseif ( ! empty( $r['group_id'] ) && bp_is_group_folders() && 'folder' === bp_action_variable( 0 ) && (int) bp_action_variable( 1 ) > 0 ) {
				$folder_id                                   = (int) bp_action_variable( 1 );
				$where_conditions_folder['user_directory']   = "a.parent = {$folder_id}";
				$where_conditions_document['user_directory'] = "m.album_id = {$folder_id}";
			} else {
				$where_conditions_document['user_directory'] = 'm.album_id = 0';
				$where_conditions_folder['user_directory']   = 'a.parent = 0';
			}
		}

		$where_conditions_folder['type']   = "a.type = 'document'";
		$where_conditions_document['type'] = "m.type = 'document'";

		if ( ! empty( $r['privacy'] ) ) {
			$privacy                              = "'" . implode( "', '", $r['privacy'] ) . "'";
			$where_conditions_document['privacy'] = "m.privacy IN ({$privacy})";
			$where_conditions_folder['privacy']   = "a.privacy IN ({$privacy})";
		}

		/**
		 * Filters the MySQL WHERE conditions for the Document items get method.
		 *
		 * @param array $where_conditions Current conditions for MySQL WHERE statement.
		 * @param array $r Parsed arguments passed into method.
		 * @param string $select_sql Current SELECT MySQL statement at point of execution.
		 * @param string $from_sql Current FROM MySQL statement at point of execution.
		 * @param string $join_sql Current INNER JOIN MySQL statement at point of execution.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		$where_conditions_document = apply_filters( 'bp_document_get_where_conditions_document', $where_conditions_document, $r, $select_sql_document, $from_sql_document, $join_sql_document );
		$where_conditions_folder   = apply_filters( 'bp_document_get_where_conditions_folder', $where_conditions_folder, $r, $select_sql_folder, $from_sql_folder, $join_sql_folder );

		if ( empty( $where_conditions_document ) ) {
			$where_conditions_document['2'] = '2';
		}

		if ( empty( $where_conditions_folder ) ) {
			$where_conditions_folder['2'] = '2';
		}

		// Join the where conditions together.
		if ( ! empty( $scope_query_document['sql'] ) && !empty( $scope_query_folder['sql'] ) ) {
			$where_sql_folder   = 'WHERE ( ' . join( ' AND ', $where_conditions_folder ) . ' ) OR ( ' . $scope_query_folder['sql'] . ' )';
			$where_sql_document = 'WHERE ( ' . join( ' AND ', $where_conditions_document ) . ' ) OR ( ' . $scope_query_document['sql'] . ' )';
		} else {
			$where_sql_document = 'WHERE ' . join( ' AND ', $where_conditions_document );
			$where_sql_folder   = 'WHERE ' . join( ' AND ', $where_conditions_folder );
		}

		/**
		 * Filter the MySQL JOIN clause for the main document query.
		 *
		 * @param string $join_sql JOIN clause.
		 * @param array $r Method parameters.
		 * @param string $select_sql Current SELECT MySQL statement.
		 * @param string $from_sql Current FROM MySQL statement.
		 * @param string $where_sql Current WHERE MySQL statement.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		$join_sql_folder   = apply_filters(
			'bp_document_get_join_sql_folder',
			$join_sql_folder,
			$r,
			$select_sql_folder,
			$from_sql_folder,
			$where_sql_folder
		);
		$join_sql_document = apply_filters(
			'bp_document_get_join_sql_document',
			$join_sql_document,
			$r,
			$select_sql_document,
			$from_sql_document,
			$where_sql_document
		);

		$retval = array(
			'documents'      => null,
			'total'          => null,
			'has_more_items' => null,
		);

		// Query first for document IDs.
		$document_ids_sql_folder   = "{$select_sql_folder} {$from_sql_folder} {$join_sql_folder} {$where_sql_folder} ORDER BY {$order_by_folder} {$sort}, a.id {$sort}";
		$document_ids_sql_document = "{$select_sql_document} {$from_sql_document} {$join_sql_document} {$where_sql_document} ORDER BY {$order_by_document} {$sort}, m.id {$sort}";

		/**
		 * Filters the paged document MySQL statement.
		 *
		 * @param string $document_ids_sql MySQL statement used to query for Document IDs.
		 * @param array $r Array of arguments passed into method.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		$document_ids_sql_folder   = apply_filters( 'bp_document_paged_activities_sql_folder', $document_ids_sql_folder, $r );
		$document_ids_sql_document = apply_filters( 'bp_document_paged_activities_sql_document', $document_ids_sql_document, $r );

		$cache_group = 'bp_document';

		$cached_folder   = bp_core_get_incremented_cache( $document_ids_sql_folder, $cache_group );
		$cached_document = bp_core_get_incremented_cache( $document_ids_sql_document, $cache_group );
		if ( false === $cached_folder ) {
			$document_ids_folder = $wpdb->get_col( $document_ids_sql_folder ); // db call ok; no-cache ok;
			bp_core_set_incremented_cache( $document_ids_sql_folder, $cache_group, $document_ids_folder );
		} else {
			$document_ids_folder = $cached_folder;
		}

		if ( false === $cached_document ) {
			$document_ids_document = $wpdb->get_col( $document_ids_sql_document ); // db call ok; no-cache ok;
			bp_core_set_incremented_cache( $document_ids_sql_document, $cache_group, $document_ids_document );
		} else {
			$document_ids_document = $cached_document;
		}

		if ( 'ids' === $r['fields'] ) {
			$documents_folder   = array_map( 'intval', $document_ids_folder );
			$documents_document = array_map( 'intval', $document_ids_document );

			$documents = array_merge( $documents_folder, $documents_document );
		} else {
			$documents_document = self::get_document_data( $document_ids_document );
			$documents_folder   = self::get_folder_data( $document_ids_folder );

			$documents = array_merge( $documents_folder, $documents_document );
		}

		if ( 'ids' !== $r['fields'] ) {
			// Get the fullnames of users so we don't have to query in the loop.
			// $documents = self::append_user_fullnames( $documents );

			// Pre-fetch data associated with document users and other objects.
			$documents = self::prefetch_object_data( $documents );
		}

		self::array_sort_by_column( $documents, 'date_created' );

		$retval['has_more_items'] = ! empty( $r['per_page'] ) && isset( $r['per_page'] ) && count( $documents ) > $r['per_page'];

		if ( isset( $r['per_page'] ) && isset( $r['page'] ) && ! empty( $r['per_page'] ) && ! empty( $r['page'] ) && $retval['has_more_items'] ) {
			$total                    = count( $documents );
			$current_page             = $r['page'];
			$item_per_page            = $r['per_page'];
			$start                    = ( $current_page - 1 ) * $item_per_page;
			$documents                = array_slice( $documents, $start, $item_per_page );
			$retval['has_more_items'] = $total > ( $current_page * $item_per_page );
			$retval['documents']      = $documents;
		} else {
			$retval['documents'] = $documents;
		}

		return $retval;
	}

	public static function array_sort_by_column( &$array, $column, $direction = SORT_DESC ) {
		$reference_array = array();

		foreach ( $array as $key => $row ) {
			$reference_array[ $key ] = $row->$column;
		}

		array_multisort( $reference_array, $direction, $array );
	}

	/**
	 * Convert document IDs to document objects, as expected in template loop.
	 *
	 * @param array $document_ids Array of document IDs.
	 *
	 * @return array
	 * @since BuddyBoss 1.3.0
	 */
	protected static function get_document_data( $document_ids = array() ) {
		global $wpdb;

		// Bail if no document ID's passed.
		if ( empty( $document_ids ) ) {
			return array();
		}

		// Get BuddyPress.
		$bp = buddypress();

		$documents    = array();
		$uncached_ids = bp_get_non_cached_ids( $document_ids, 'bp_document' );

		// Prime caches as necessary.
		if ( ! empty( $uncached_ids ) ) {
			// Format the document ID's for use in the query below.
			$uncached_ids_sql = implode( ',', wp_parse_id_list( $uncached_ids ) );

			// Fetch data from document table, preserving order.
			$queried_adata = $wpdb->get_results( "SELECT * FROM {$bp->document->table_name} WHERE id IN ({$uncached_ids_sql})" ); // db call ok; no-cache ok;

			// Put that data into the placeholders created earlier,
			// and add it to the cache.
			foreach ( (array) $queried_adata as $adata ) {
				wp_cache_set( $adata->id, $adata, 'bp_document' );
			}
		}

		// Now fetch data from the cache.
		foreach ( $document_ids as $document_id ) {
			// Integer casting.
			$document = wp_cache_get( $document_id, 'bp_document' );
			if ( ! empty( $document ) ) {
				$document->id            = (int) $document->id;
				$document->blog_id       = (int) $document->blog_id;
				$document->user_id       = (int) $document->user_id;
				$document->attachment_id = (int) $document->attachment_id;
				$document->folder_id     = (int) $document->album_id;
				$document->activity_id   = (int) $document->activity_id;
				$document->group_id      = (int) $document->group_id;
				$document->menu_order    = (int) $document->menu_order;
			}

			// fetch attachment data
			$attachment_data                 = new stdClass();
			$attachment_data->full           = '';
			$attachment_data->thumb          = '';
			$attachment_data->activity_thumb = '';
			$attachment_data->meta           = wp_get_attachment_metadata( $document->attachment_id );
			$document->attachment_data       = $attachment_data;

			$documents[] = $document;
		}

		// Then fetch user data.
		$user_query = new BP_User_Query(
			array(
				'user_ids'        => wp_list_pluck( $documents, 'user_id' ),
				'populate_extras' => false,
			)
		);

		// Associated located user data with document items.
		foreach ( $documents as $a_index => $a_item ) {
			$a_user_id = intval( $a_item->user_id );
			$a_user    = isset( $user_query->results[ $a_user_id ] ) ? $user_query->results[ $a_user_id ] : '';

			if ( ! empty( $a_user ) ) {
				$documents[ $a_index ]->user_email    = $a_user->user_email;
				$documents[ $a_index ]->user_nicename = $a_user->user_nicename;
				$documents[ $a_index ]->user_login    = $a_user->user_login;
				$documents[ $a_index ]->display_name  = $a_user->display_name;
			}
		}

		return $documents;
	}

	/**
	 * Convert document IDs to document objects, as expected in template loop.
	 *
	 * @param array $document_ids Array of document IDs.
	 *
	 * @return array
	 * @since BuddyBoss 1.3.0
	 */
	protected static function get_folder_data( $folder_ids = array() ) {
		global $wpdb;

		// Bail if no document ID's passed.
		if ( empty( $folder_ids ) ) {
			return array();
		}

		// Get BuddyPress.
		$bp = buddypress();

		$documents    = array();
		$uncached_ids = bp_get_non_cached_ids( $folder_ids, 'bp_document_folder' );

		// Prime caches as necessary.
		if ( ! empty( $uncached_ids ) ) {
			// Format the document ID's for use in the query below.
			$uncached_ids_sql = implode( ',', wp_parse_id_list( $uncached_ids ) );

			// Fetch data from document table, preserving order.
			$queried_adata = $wpdb->get_results( "SELECT * FROM {$bp->document->table_name_folders} WHERE id IN ({$uncached_ids_sql})" ); // db call ok; no-cache ok;

			// Put that data into the placeholders created earlier,
			// and add it to the cache.
			foreach ( (array) $queried_adata as $adata ) {
				wp_cache_set( $adata->id, $adata, 'bp_document_folder' );
			}
		}

		// Now fetch data from the cache.
		foreach ( $folder_ids as $document_id ) {
			// Integer casting.
			$document = wp_cache_get( $document_id, 'bp_document_folder' );
			if ( ! empty( $document ) ) {
				$document->id           = (int) $document->id;
				$document->user_id      = (int) $document->user_id;
				$document->group_id     = (int) $document->group_id;
				$document->date_created = $document->date_created;
				$document->title        = $document->title;
				$document->privacy      = $document->privacy;
				$document->parent       = $document->parent;

				if ( (int) $document->group_id > 0 ) {
					$document->folder = 'group';
					$group            = groups_get_group( array( 'group_id' => $document->group_id ) );
					$document->link   = bp_get_group_permalink( $group ) . bp_get_document_slug() . '/folder/' . (int) $document->id;
				} else {
					$document->folder = 'profile';
					$document->link   = bp_core_get_user_domain( (int) $document->user_id ) . bp_get_document_slug() . '/folder/' . (int) $document->id;
				}
			}
			$documents[] = $document;
		}

		// Then fetch user data.
		$user_query = new BP_User_Query(
			array(
				'user_ids'        => wp_list_pluck( $documents, 'user_id' ),
				'populate_extras' => false,
			)
		);

		// Associated located user data with document items.
		foreach ( $documents as $a_index => $a_item ) {
			$a_user_id = intval( $a_item->user_id );
			$a_user    = isset( $user_query->results[ $a_user_id ] ) ? $user_query->results[ $a_user_id ] : '';

			if ( ! empty( $a_user ) ) {
				$documents[ $a_index ]->user_email    = $a_user->user_email;
				$documents[ $a_index ]->user_nicename = $a_user->user_nicename;
				$documents[ $a_index ]->user_login    = $a_user->user_login;
				$documents[ $a_index ]->display_name  = $a_user->display_name;
			}
		}

		return $documents;
	}

	/**
	 * Append xProfile fullnames to an document array.
	 *
	 * @param array $documents Documents array.
	 *
	 * @return array
	 * @since BuddyBoss 1.3.0
	 */
	protected static function append_user_fullnames( $documents ) {

		if ( bp_is_active( 'xprofile' ) && ! empty( $documents ) ) {
			$document_user_ids = wp_list_pluck( $documents, 'user_id' );

			if ( ! empty( $document_user_ids ) ) {
				$fullnames = bp_core_get_user_displaynames( $document_user_ids );
				if ( ! empty( $fullnames ) ) {
					foreach ( (array) $documents as $i => $document ) {
						if ( ! empty( $fullnames[ $document->user_id ] ) ) {
							$documents[ $i ]->user_fullname = $fullnames[ $document->user_id ];
						}
					}
				}
			}
		}

		return $documents;
	}

	/**
	 * Pre-fetch data for objects associated with document items.
	 *
	 * Document items are associated with users, and often with other
	 * BuddyPress data objects. Here, we pre-fetch data about these
	 * associated objects, so that inline lookups - done primarily when
	 * building action strings - do not result in excess database queries.
	 *
	 * @param array $documents Array of document.
	 *
	 * @return array $documents Array of document.
	 * @since BuddyBoss 1.3.0
	 */
	protected static function prefetch_object_data( $documents ) {

		/**
		 * Filters inside prefetch_object_data method to aid in pre-fetching object data associated with document item.
		 *
		 * @param array $documents Array of document.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		return apply_filters( 'bp_document_prefetch_object_data', $documents );
	}

	/**
	 * Get the SQL for the 'scope' param in BP_Document::get().
	 *
	 * A scope is a predetermined set of document arguments.  This method is used
	 * to grab these document arguments and override any existing args if needed.
	 *
	 * Can handle multiple scopes.
	 *
	 * @param mixed $scope The document scope. Accepts string or array of scopes.
	 * @param array $r Current activity arguments. Same as those of BP_document::get(),
	 *                       but merged with defaults.
	 *
	 * @return false|array 'sql' WHERE SQL string and 'override' document args.
	 * @since BuddyBoss 1.1.9
	 */
	public static function get_scope_query_sql( $scope = false, $r = array() ) {

		// Define arrays for future use.
		$query_args = array();
		$override   = array();
		$retval     = array();

		// Check for array of scopes.
		if ( is_array( $scope ) ) {
			$scopes = $scope;

			// Explode a comma separated string of scopes.
		} elseif ( is_string( $scope ) ) {
			$scopes = explode( ',', $scope );
		}

		// Bail if no scope passed.
		if ( empty( $scopes ) ) {
			return false;
		}

		// Helper to easily grab the 'user_id'.
		if ( ! empty( $r['filter']['user_id'] ) ) {
			$r['user_id'] = $r['filter']['user_id'];
		}

		// Parse each scope; yes! we handle multiples!
		foreach ( $scopes as $scope ) {
			$scope_args = array();

			/**
			 * Plugins can hook here to set their document arguments for custom scopes.
			 *
			 * This is a dynamic filter based on the document scope. eg:
			 *   - 'bp_document_set_groups_scope_args'
			 *   - 'bp_document_set_friends_scope_args'
			 *
			 * To see how this filter is used, plugin devs should check out:
			 *   - bp_groups_filter_document_scope() - used for 'groups' scope
			 *   - bp_friends_filter_document_scope() - used for 'friends' scope
			 *
			 * @param array {
			 *     Document query clauses.
			 *
			 * @type array {
			 *         Document arguments for your custom scope.
			 *         See {@link BP_Document_Query::_construct()} for more details.
			 *     }
			 * @type array $override Optional. Override existing document arguments passed by $r.
			 *     }
			 * }
			 *
			 * @param array $r Current activity arguments passed in BP_Document::get().
			 *
			 * @since BuddyBoss 1.1.9
			 */
			$scope_args = apply_filters( "bp_document_set_{$scope}_scope_args", array(), $r );

			if ( ! empty( $scope_args ) ) {
				// Merge override properties from other scopes
				// this might be a problem...
				if ( ! empty( $scope_args['override'] ) ) {
					$override = array_merge( $override, $scope_args['override'] );
					unset( $scope_args['override'] );
				}

				// Save scope args.
				if ( ! empty( $scope_args ) ) {
					$query_args[] = $scope_args;
				}
			}
		}

		if ( ! empty( $query_args ) ) {
			// Set relation to OR.
			$query_args['relation'] = 'OR';

			$query = new BP_Document_Query( $query_args );
			$sql   = $query->get_sql();
			if ( ! empty( $sql ) ) {
				$retval['sql'] = $sql;
			}
		}

		if ( ! empty( $override ) ) {
			$retval['override'] = $override;
		}

		return $retval;
	}

	/**
	 * Get the SQL for the 'scope' param in BP_Document::get().
	 *
	 * A scope is a predetermined set of document arguments.  This method is used
	 * to grab these document arguments and override any existing args if needed.
	 *
	 * Can handle multiple scopes.
	 *
	 * @param mixed $scope The document scope. Accepts string or array of scopes.
	 * @param array $r Current activity arguments. Same as those of BP_document::get(),
	 *                       but merged with defaults.
	 *
	 * @return false|array 'sql' WHERE SQL string and 'override' document args.
	 * @since BuddyBoss 1.1.9
	 */
	public static function get_scope_document_query_sql( $scope = false, $r = array() ) {

		// Define arrays for future use.
		$query_args = array();
		$override   = array();
		$retval     = array();

		// Check for array of scopes.
		if ( is_array( $scope ) ) {
			$scopes = $scope;

			// Explode a comma separated string of scopes.
		} elseif ( is_string( $scope ) ) {
			$scopes = explode( ',', $scope );
		}

		// Bail if no scope passed.
		if ( empty( $scopes ) ) {
			return false;
		}

		// Helper to easily grab the 'user_id'.
		if ( ! empty( $r['filter']['user_id'] ) ) {
			$r['user_id'] = $r['filter']['user_id'];
		}

		// Parse each scope; yes! we handle multiples!
		foreach ( $scopes as $scope ) {
			$scope_args = array();

			/**
			 * Plugins can hook here to set their document arguments for custom scopes.
			 *
			 * This is a dynamic filter based on the document scope. eg:
			 *   - 'bp_document_set_groups_scope_args'
			 *   - 'bp_document_set_friends_scope_args'
			 *
			 * To see how this filter is used, plugin devs should check out:
			 *   - bp_groups_filter_document_scope() - used for 'groups' scope
			 *   - bp_friends_filter_document_scope() - used for 'friends' scope
			 *
			 * @param array {
			 *     Document query clauses.
			 *
			 * @type array {
			 *         Document arguments for your custom scope.
			 *         See {@link BP_Document_Query::_construct()} for more details.
			 *     }
			 * @type array $override Optional. Override existing document arguments passed by $r.
			 *     }
			 * }
			 *
			 * @param array $r Current activity arguments passed in BP_Document::get().
			 *
			 * @since BuddyBoss 1.1.9
			 */
			$scope_args = apply_filters( "bp_document_set_document_{$scope}_scope_args", array(), $r );

			if ( ! empty( $scope_args ) ) {
				// Merge override properties from other scopes
				// this might be a problem...
				if ( ! empty( $scope_args['override'] ) ) {
					$override = array_merge( $override, $scope_args['override'] );
					unset( $scope_args['override'] );
				}

				// Save scope args.
				if ( ! empty( $scope_args ) ) {
					$query_args[] = $scope_args;
				}
			}
		}

		if ( ! empty( $query_args ) ) {
			// Set relation to OR.
			$query_args['relation'] = 'OR';

			$query = new BP_Document_Query( $query_args );
			$sql   = $query->get_sql_document();
			if ( ! empty( $sql ) ) {
				$retval['sql'] = $sql;
			}
		}

		if ( ! empty( $override ) ) {
			$retval['override'] = $override;
		}

		return $retval;
	}

	/**
	 * Get the SQL for the 'scope' param in BP_Document::get().
	 *
	 * A scope is a predetermined set of document arguments.  This method is used
	 * to grab these document arguments and override any existing args if needed.
	 *
	 * Can handle multiple scopes.
	 *
	 * @param mixed $scope The document scope. Accepts string or array of scopes.
	 * @param array $r Current activity arguments. Same as those of BP_document::get(),
	 *                       but merged with defaults.
	 *
	 * @return false|array 'sql' WHERE SQL string and 'override' document args.
	 * @since BuddyBoss 1.1.9
	 */
	public static function get_scope_folder_query_sql( $scope = false, $r = array() ) {

		// Define arrays for future use.
		$query_args = array();
		$override   = array();
		$retval     = array();

		// Check for array of scopes.
		if ( is_array( $scope ) ) {
			$scopes = $scope;

			// Explode a comma separated string of scopes.
		} elseif ( is_string( $scope ) ) {
			$scopes = explode( ',', $scope );
		}

		// Bail if no scope passed.
		if ( empty( $scopes ) ) {
			return false;
		}

		// Helper to easily grab the 'user_id'.
		if ( ! empty( $r['filter']['user_id'] ) ) {
			$r['user_id'] = $r['filter']['user_id'];
		}

		// Parse each scope; yes! we handle multiples!
		foreach ( $scopes as $scope ) {
			$scope_args = array();

			/**
			 * Plugins can hook here to set their document arguments for custom scopes.
			 *
			 * This is a dynamic filter based on the document scope. eg:
			 *   - 'bp_document_set_groups_scope_args'
			 *   - 'bp_document_set_friends_scope_args'
			 *
			 * To see how this filter is used, plugin devs should check out:
			 *   - bp_groups_filter_document_scope() - used for 'groups' scope
			 *   - bp_friends_filter_document_scope() - used for 'friends' scope
			 *
			 * @param array {
			 *     Document query clauses.
			 *
			 * @type array {
			 *         Document arguments for your custom scope.
			 *         See {@link BP_Document_Query::_construct()} for more details.
			 *     }
			 * @type array $override Optional. Override existing document arguments passed by $r.
			 *     }
			 * }
			 *
			 * @param array $r Current activity arguments passed in BP_Document::get().
			 *
			 * @since BuddyBoss 1.1.9
			 */
			$scope_args = apply_filters( "bp_document_set_folder_{$scope}_scope_args", array(), $r );

			if ( ! empty( $scope_args ) ) {
				// Merge override properties from other scopes
				// this might be a problem...
				if ( ! empty( $scope_args['override'] ) ) {
					$override = array_merge( $override, $scope_args['override'] );
					unset( $scope_args['override'] );
				}

				// Save scope args.
				if ( ! empty( $scope_args ) ) {
					$query_args[] = $scope_args;
				}
			}
		}

		if ( ! empty( $query_args ) ) {
			// Set relation to OR.
			$query_args['relation'] = 'OR';

			$query = new BP_Document_Query( $query_args );
			$sql   = $query->get_sql_folder();
			if ( ! empty( $sql ) ) {
				$retval['sql'] = $sql;
			}
		}

		if ( ! empty( $override ) ) {
			$retval['override'] = $override;
		}

		return $retval;
	}

	/**
	 * Create SQL IN clause for filter queries.
	 *
	 * @param string     $field The database field.
	 * @param array|bool $items The values for the IN clause, or false when none are found.
	 *
	 * @return string|false
	 * @see BP_Document::get_filter_sql()
	 *
	 * @since BuddyBoss 1.1.9
	 */
	public static function get_in_operator_sql( $field, $items ) {
		global $wpdb;

		// Split items at the comma.
		if ( ! is_array( $items ) ) {
			$items = explode( ',', $items );
		}

		// Array of prepared integers or quoted strings.
		$items_prepared = array();

		// Clean up and format each item.
		foreach ( $items as $item ) {
			// Clean up the string.
			$item = trim( $item );
			// Pass everything through prepare for security and to safely quote strings.
			$items_prepared[] = ( is_numeric( $item ) ) ? $wpdb->prepare( '%d', $item ) : $wpdb->prepare( '%s', $item );
		}

		// Build IN operator sql syntax.
		if ( count( $items_prepared ) ) {
			return sprintf( '%s IN ( %s )', trim( $field ), implode( ',', $items_prepared ) );
		} else {
			return false;
		}
	}

	/**
	 * Delete document items from the database.
	 *
	 * To delete a specific document item, pass an 'id' parameter.
	 * Otherwise use the filters.
	 *
	 * @param array $args {
	 * @int    $id                Optional. The ID of a specific item to delete.
	 * @int    $blog_id           Optional. The blog ID to filter by.
	 * @int    $attachment_id           Optional. The attachment ID to filter by.
	 * @int    $user_id           Optional. The user ID to filter by.
	 * @string    $title           Optional. The title to filter by.
	 * @int    $folder_id           Optional. The folder ID to filter by.
	 * @int    $activity_id           Optional. The activity ID to filter by.
	 * @int    $group_id           Optional. The group ID to filter by.
	 * @string    $privacy           Optional. The privacy to filter by.
	 * @string $date_created      Optional. The date to filter by.
	 * }
	 * @param bool  $from Context of deletion from. ex. attachment, activity etc.
	 *
	 * @return array|bool An array of deleted document IDs on success, false on failure.
	 * @since BuddyBoss 1.3.0
	 */
	public static function delete( $args = array(), $from = false ) {
		global $wpdb;

		$bp = buddypress();
		$r  = wp_parse_args(
			$args,
			array(
				'id'            => false,
				'blog_id'       => false,
				'attachment_id' => false,
				'user_id'       => false,
				'title'         => false,
				'folder_id'     => false,
				'activity_id'   => false,
				'group_id'      => false,
				'privacy'       => false,
				'date_created'  => false,
			)
		);

		// Setup empty array from where query arguments.
		$where_args = array();

		// ID.
		if ( ! empty( $r['id'] ) ) {
			$where_args[] = $wpdb->prepare( 'id = %d', $r['id'] );
		}

		// blog ID.
		if ( ! empty( $r['blog_id'] ) ) {
			$where_args[] = $wpdb->prepare( 'blog_id = %d', $r['blog_id'] );
		}

		// attachment ID.
		if ( ! empty( $r['attachment_id'] ) ) {
			$where_args[] = $wpdb->prepare( 'attachment_id = %d', $r['attachment_id'] );
		}

		// User ID.
		if ( ! empty( $r['user_id'] ) ) {
			$where_args[] = $wpdb->prepare( 'user_id = %d', $r['user_id'] );
		}

		// title.
		if ( ! empty( $r['title'] ) ) {
			$where_args[] = $wpdb->prepare( 'title = %s', $r['title'] );
		}

		// folder ID.
		if ( ! empty( $r['folder_id'] ) ) {
			$where_args[] = $wpdb->prepare( 'album_id = %d', $r['folder_id'] );
		}

		// activity ID.
		if ( ! empty( $r['activity_id'] ) ) {
			$where_args[] = $wpdb->prepare( 'activity_id = %d', $r['activity_id'] );
		}

		// group ID.
		if ( ! empty( $r['group_id'] ) ) {
			$where_args[] = $wpdb->prepare( 'group_id = %d', $r['group_id'] );
		}

		// privacy.
		if ( ! empty( $r['privacy'] ) ) {
			$where_args[] = $wpdb->prepare( 'privacy = %s', $r['privacy'] );
		}

		// Date created.
		if ( ! empty( $r['date_created'] ) ) {
			$where_args[] = $wpdb->prepare( 'date_created = %s', $r['date_created'] );
		}

		// Bail if no where arguments.
		if ( empty( $where_args ) ) {
			return false;
		}

		// Join the where arguments for querying.
		$where_sql = 'WHERE ' . join( ' AND ', $where_args );

		// Fetch all document being deleted so we can perform more actions.
		$documents = $wpdb->get_results( "SELECT * FROM {$bp->document->table_name} {$where_sql}" ); // db call ok; no-cache ok;

		/**
		 * Action to allow intercepting document items to be deleted.
		 *
		 * @param array $documents Array of document.
		 * @param array $r Array of parsed arguments.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		do_action_ref_array( 'bp_document_before_delete', array( $documents, $r ) );

		// Attempt to delete document from the database.
		$deleted = $wpdb->query( "DELETE FROM {$bp->document->table_name} {$where_sql}" ); // db call ok; no-cache ok;

		// Bail if nothing was deleted.
		if ( empty( $deleted ) ) {
			return false;
		}

		/**
		 * Action to allow intercepting document items just deleted.
		 *
		 * @param array $documents Array of document.
		 * @param array $r Array of parsed arguments.
		 *
		 * @since BuddyBoss 1.3.0
		 */
		do_action_ref_array( 'bp_document_after_delete', array( $documents, $r ) );

		// Pluck the document IDs out of the $documents array.
		$document_ids   = wp_parse_id_list( wp_list_pluck( $documents, 'id' ) );
		$activity_ids   = wp_parse_id_list( wp_list_pluck( $documents, 'activity_id' ) );
		$attachment_ids = wp_parse_id_list( wp_list_pluck( $documents, 'attachment_id' ) );

		// Handle accompanying attachments and meta deletion.
		if ( ! empty( $attachment_ids ) ) {

			// Loop through attachment ids and attempt to delete.
			foreach ( $attachment_ids as $attachment_id ) {

				if ( bp_is_active( 'activity' ) ) {
					$parent_activity_id = get_post_meta( $attachment_id, 'bp_document_parent_activity_id', true );
					if ( ! empty( $parent_activity_id ) ) {
						$activity_document_ids = bp_activity_get_meta( $parent_activity_id, 'bp_document_ids', true );
						if ( ! empty( $activity_document_ids ) ) {
							$activity_document_ids = explode( ',', $activity_document_ids );
							$activity_document_ids = array_diff( $activity_document_ids, $document_ids );
							if ( ! empty( $activity_document_ids ) ) {
								$activity_document_ids = implode( ',', $activity_document_ids );
								bp_activity_update_meta(
									$parent_activity_id,
									'bp_document_ids',
									$activity_document_ids
								);
							} else {
								$activity_ids[] = $parent_activity_id;
							}
						}
					}
				}

				if ( empty( $from ) ) {
					wp_delete_attachment( $attachment_id, true );
				}
			}
		}

		// delete related activity
		if ( ! empty( $activity_ids ) && bp_is_active( 'activity' ) ) {

			foreach ( $activity_ids as $activity_id ) {
				$activity = new BP_Activity_Activity( (int) $activity_id );

				// Check access.
				if ( bp_activity_user_can_delete( $activity ) ) {
					/** This action is documented in bp-activity/bp-activity-actions.php */
					do_action( 'bp_activity_before_action_delete_activity', $activity->id, $activity->user_id );

					// Deleting an activity comment.
					if ( 'activity_comment' == $activity->type ) {
						if ( bp_activity_delete_comment( $activity->item_id, $activity->id ) ) {
							/** This action is documented in bp-activity/bp-activity-actions.php */
							do_action( 'bp_activity_action_delete_activity', $activity->id, $activity->user_id );
						}

						// Deleting an activity.
					} else {
						if ( bp_activity_delete(
							array(
								'id'      => $activity->id,
								'user_id' => $activity->user_id,
							)
						) ) {
							/** This action is documented in bp-activity/bp-activity-actions.php */
							do_action( 'bp_activity_action_delete_activity', $activity->id, $activity->user_id );
						}
					}
				}
			}
		}

		return $document_ids;
	}

	/**
	 * Count total document for the given user
	 *
	 * @param int $user_id
	 *
	 * @return array|bool|int
	 * @since BuddyBoss 1.3.0
	 */
	public static function total_document_count( $user_id = 0 ) {
		global $bp, $wpdb;

		$privacy = array( 'public' );
		if ( is_user_logged_in() ) {
			$privacy[] = 'loggedin';
			if ( bp_is_active( 'friends' ) ) {
				$is_friend = friends_check_friendship( get_current_user_id(), $user_id );
				if ( $is_friend ) {
					$privacy[] = 'friends';
				}
			}
		}
		$privacy = "'" . implode( "', '", $privacy ) . "'";

		$total_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$bp->document->table_name} WHERE user_id = {$user_id} AND privacy IN ({$privacy}) AND type = 'document'" ); // db call ok; no-cache ok;

		return $total_count;
	}

	/**
	 * Count total document for the given group
	 *
	 * @param int $group_id
	 *
	 * @return array|bool|int
	 * @since BuddyBoss 1.3.0
	 */
	public static function total_group_document_count( $group_id = 0 ) {
		global $bp, $wpdb;

		$total_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$bp->document->table_name} WHERE group_id = {$group_id}" ); // db call ok; no-cache ok;

		return $total_count;
	}

	/**
	 * Get all document ids for the folder
	 *
	 * @param bool $folder_id
	 *
	 * @return array|bool
	 * @since BuddyBoss 1.3.0
	 */
	public static function get_folder_document_ids( $folder_id = false ) {
		global $bp, $wpdb;

		if ( ! $folder_id ) {
			return false;
		}

		$folder_document_sql = $wpdb->prepare(
			"SELECT DISTINCT m.id FROM {$bp->document->table_name} m WHERE m.album_id = %d",
			$folder_id
		);

		$cached = bp_core_get_incremented_cache( $folder_document_sql, 'bp_document' );

		if ( false === $cached ) {
			$document_ids = $wpdb->get_col( $folder_document_sql ); // db call ok; no-cache ok;
			bp_core_set_incremented_cache( $folder_document_sql, 'bp_document', $document_ids );
		} else {
			$document_ids = $cached;
		}

		return (array) $document_ids;
	}

	/**
	 * Get document id for the activity.
	 *
	 * @param bool $activity_id
	 *
	 * @return array|bool
	 * @since BuddyBoss 1.1.6
	 */
	public static function get_activity_document_id( $activity_id = false ) {
		global $bp, $wpdb;

		if ( ! $activity_id ) {
			return false;
		}

		$activity_document_id = (int) $wpdb->get_var( "SELECT DISTINCT m.id FROM {$bp->document->table_name} m WHERE m.activity_id = {$activity_id}" ); // db call ok; no-cache ok;

		return $activity_document_id;
	}

}