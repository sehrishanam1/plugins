<?php
/**
 * Shortcode: [team_members]
 *
 * Usage examples:
 *   [team_members]
 *   [team_members limit="6" orderby="custom_order" columns="2"]
 *   [team_members department="engineering" limit="3"]
 *
 * @package TeamMembers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TM_Shortcode {

	public static function init() {
		add_shortcode( 'team_members', array( __CLASS__, 'render' ) );
	}

	/**
	 * Build and return the team members HTML.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @return string       HTML output.
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'      => 3,         // Number of members to show (-1 = all)
				'orderby'    => 'date',    // 'date' | 'custom_order' | 'title'
				'order'      => 'DESC',    // ASC | DESC
				'columns'    => 3,         // Grid columns
				'department' => '',        // Taxonomy slug filter
			),
			$atts,
			'team_members'
		);

		$members = self::get_members( $atts );

		if ( empty( $members ) ) {
			return '<p class="tm-no-members">' . esc_html__( 'No team members found.', 'team-members' ) . '</p>';
		}

		ob_start();
		include TM_PLUGIN_DIR . 'templates/team-grid.php';
		return ob_get_clean();
	}

	/**
	 * Query team members via WP_Query.
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return WP_Post[]
	 */
	public static function get_members( $atts ) {
		$query_args = array(
			'post_type'      => 'team_member',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['limit'] ),
		);

		// Sorting
		switch ( $atts['orderby'] ) {
			case 'custom_order':
				$query_args['orderby']  = 'meta_value_num';
				$query_args['meta_key'] = '_tm_order';
				$query_args['order']    = 'ASC';
				break;

			case 'title':
				$query_args['orderby'] = 'title';
				$query_args['order']   = sanitize_text_field( $atts['order'] );
				break;

			case 'date':
			default:
				$query_args['orderby'] = 'date';
				$query_args['order']   = sanitize_text_field( $atts['order'] );
				break;
		}

		// Taxonomy filter
		if ( ! empty( $atts['department'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'tm_department',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['department'] ),
				),
			);
		}

		$query = new WP_Query( $query_args );
		return $query->posts;
	}
}

TM_Shortcode::init();
