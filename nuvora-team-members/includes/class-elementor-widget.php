<?php
/**
 * Elementor Widget – Team Members Grid.
 *
 * Requires Elementor >= 3.0.
 *
 * @package TeamMembers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

class TM_Elementor_Widget extends Widget_Base {

	public function get_name()  { return 'tm_team_members'; }
	public function get_title() { return esc_html__( 'Team Members', 'team-members' ); }
	public function get_icon()  { return 'eicon-person'; }
	public function get_categories() { return array( 'general' ); }
	public function get_keywords()   { return array( 'team', 'members', 'staff', 'card' ); }

	// ── Controls ──────────────────────────────────────────────────────────────

	protected function register_controls() {

		// ─ Content: Query ────────────────────────────────────────────────────
		$this->start_controls_section( 'section_query', array(
			'label' => esc_html__( 'Query', 'team-members' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'limit', array(
			'label'   => esc_html__( 'Number of Members', 'team-members' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 50,
			'default' => 3,
		) );

		$this->add_control( 'orderby', array(
			'label'   => esc_html__( 'Order By', 'team-members' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'date',
			'options' => array(
				'date'         => esc_html__( 'Date (Newest First)', 'team-members' ),
				'custom_order' => esc_html__( 'Custom Order', 'team-members' ),
				'title'        => esc_html__( 'Name (A–Z)', 'team-members' ),
			),
		) );

		$this->end_controls_section();

		// ─ Content: Layout ───────────────────────────────────────────────────
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'team-members' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'columns', array(
			'label'   => esc_html__( 'Columns', 'team-members' ),
			'type'    => Controls_Manager::SELECT,
			'default' => '3',
			'options' => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
		) );

		$this->add_control( 'show_description', array(
			'label'        => esc_html__( 'Show Description', 'team-members' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Show', 'team-members' ),
			'label_off'    => esc_html__( 'Hide', 'team-members' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->add_control( 'show_social', array(
			'label'        => esc_html__( 'Show Social Links', 'team-members' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Show', 'team-members' ),
			'label_off'    => esc_html__( 'Hide', 'team-members' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->end_controls_section();

		// ─ Style: Card ───────────────────────────────────────────────────────
		$this->start_controls_section( 'section_style_card', array(
			'label' => esc_html__( 'Card', 'team-members' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'card_bg', array(
			'label'     => esc_html__( 'Background', 'team-members' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .tm-card' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'card_radius', array(
			'label'      => esc_html__( 'Border Radius', 'team-members' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'default'    => array( 'top'=>12,'right'=>12,'bottom'=>12,'left'=>12,'unit'=>'px','isLinked'=>true ),
			'selectors'  => array( '{{WRAPPER}} .tm-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_shadow',
			'label'    => esc_html__( 'Box Shadow', 'team-members' ),
			'selector' => '{{WRAPPER}} .tm-card',
		) );

		$this->add_control( 'card_gap', array(
			'label'      => esc_html__( 'Gap Between Cards', 'team-members' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'default'    => array( 'unit' => 'px', 'size' => 24 ),
			'selectors'  => array( '{{WRAPPER}} .tm-grid' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();

		// ─ Style: Typography ─────────────────────────────────────────────────
		$this->start_controls_section( 'section_style_typo', array(
			'label' => esc_html__( 'Typography', 'team-members' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'name_color', array(
			'label'     => esc_html__( 'Name Color', 'team-members' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#1a1a2e',
			'selectors' => array( '{{WRAPPER}} .tm-card__name' => 'color: {{VALUE}};' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'name_typography',
			'label'    => esc_html__( 'Name Typography', 'team-members' ),
			'selector' => '{{WRAPPER}} .tm-card__name',
		) );

		$this->add_control( 'position_color', array(
			'label'     => esc_html__( 'Position Color', 'team-members' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#6c63ff',
			'selectors' => array( '{{WRAPPER}} .tm-card__position' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	// ── Render ────────────────────────────────────────────────────────────────

	protected function render() {
		$settings = $this->get_settings_for_display();

		$atts = array(
			'limit'      => $settings['limit'],
			'orderby'    => $settings['orderby'],
			'order'      => 'DESC',
			'columns'    => $settings['columns'],
			'department' => '',
		);

		$members = TM_Shortcode::get_members( $atts );

		if ( empty( $members ) ) {
			echo '<p class="tm-no-members">' . esc_html__( 'No team members found.', 'team-members' ) . '</p>';
			return;
		}

		$show_desc   = $settings['show_description'] === 'yes';
		$show_social = $settings['show_social'] === 'yes';
		$columns     = intval( $settings['columns'] );

		include TM_PLUGIN_DIR . 'templates/team-grid.php';
	}
}

// Widget is registered in team-members.php via tm_register_elementor_widget()
