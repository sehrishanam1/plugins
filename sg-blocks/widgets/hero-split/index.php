<?php
/**
 * Widget: Hero Split
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_hero_split_register' );

function sg_hero_split_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/hero-split/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/hero-split/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';

    wp_register_script(
        'sg-hero-split-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-hero-split-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/hero-split', array(
        'editor_script'   => 'sg-hero-split-editor',
        'editor_style'    => 'sg-hero-split-style',
        'style'           => 'sg-hero-split-style',
        'render_callback' => 'sg_hero_split_render',
        'attributes'      => array(
            'innerWidth'      => array( 'type' => 'string',  'default' => '1200px' ),
            'bgImage'         => array( 'type' => 'string',  'default' => '' ),
            'bgImageId'       => array( 'type' => 'integer', 'default' => 0 ),
            'showBreadcrumb'  => array( 'type' => 'boolean', 'default' => true ),
            'titleLine1'      => array( 'type' => 'string',  'default' => 'You Deserve a Website' ),
            'titleLine2'      => array( 'type' => 'string',  'default' => '{That Actually Wins Clients}' ),
            'primaryColor'    => array( 'type' => 'string',  'default' => '#00c8ff' ),
            'description'     => array( 'type' => 'string',  'default' => 'Most websites in your industry look the same, load slowly, and lose leads the moment someone clicks. We build high-converting websites built around your buyer — not templates.' ),
            'showIconList'    => array( 'type' => 'boolean', 'default' => true ),
            'iconItems'       => array( 'type' => 'string',  'default' => '[]' ),
            'showBtn1'        => array( 'type' => 'boolean', 'default' => true ),
            'btn1Text'        => array( 'type' => 'string',  'default' => 'Start My Website' ),
            'btn1Url'         => array( 'type' => 'string',  'default' => '#' ),
            'btn1Style'       => array( 'type' => 'string',  'default' => 'solid' ),
            'showBtn2'        => array( 'type' => 'boolean', 'default' => true ),
            'btn2Text'        => array( 'type' => 'string',  'default' => 'See Past Projects' ),
            'btn2Url'         => array( 'type' => 'string',  'default' => '#' ),
            'btn2Style'       => array( 'type' => 'string',  'default' => 'outline' ),
            'showStars'       => array( 'type' => 'boolean', 'default' => true ),
            'starsText'       => array( 'type' => 'string',  'default' => 'trusted by contractors, clinics & consultants across Dubai' ),
            'starsCount'      => array( 'type' => 'number',  'default' => 5 ),
            'starsColor'      => array( 'type' => 'string',  'default' => '#f5a623' ),
        ),
    ) );
}

/**
 * Build a real breadcrumb trail from WordPress page hierarchy.
 * Returns HTML like: <a href="/">Home</a> / <a href="/parent/">Parent</a> / Current Page
 */
function sg_hero_split_breadcrumb() {
    $trail = array();

    // Home link always first
    $trail[] = '<a class="sg-hs__breadcrumb-link" href="' . esc_url( home_url( '/' ) ) . '">Home</a>';

    $post = get_post();

    if ( is_singular() && $post ) {

        // Build ancestor chain for pages (parent -> grandparent etc.)
        if ( $post->post_type === 'page' && $post->post_parent ) {
            $ancestors = array_reverse( get_post_ancestors( $post ) );
            foreach ( $ancestors as $ancestor_id ) {
                $trail[] = '<a class="sg-hs__breadcrumb-link" href="' . esc_url( get_permalink( $ancestor_id ) ) . '">'
                         . esc_html( get_the_title( $ancestor_id ) )
                         . '</a>';
            }
        }

        // For posts: add category
        if ( $post->post_type === 'post' ) {
            $cats = get_the_category( $post->ID );
            if ( ! empty( $cats ) ) {
                $trail[] = '<a class="sg-hs__breadcrumb-link" href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">'
                         . esc_html( $cats[0]->name )
                         . '</a>';
            }
        }

        // Current page title (not linked)
        $trail[] = '<span class="sg-hs__breadcrumb-current">' . esc_html( get_the_title( $post ) ) . '</span>';

    } elseif ( is_category() ) {
        $trail[] = '<span class="sg-hs__breadcrumb-current">' . esc_html( single_cat_title( '', false ) ) . '</span>';

    } elseif ( is_tag() ) {
        $trail[] = '<span class="sg-hs__breadcrumb-current">' . esc_html( single_tag_title( '', false ) ) . '</span>';

    } elseif ( is_archive() ) {
        $trail[] = '<span class="sg-hs__breadcrumb-current">' . esc_html( get_the_archive_title() ) . '</span>';

    } elseif ( is_search() ) {
        $trail[] = '<span class="sg-hs__breadcrumb-current">Search: ' . esc_html( get_search_query() ) . '</span>';
    }

    $separator = '<span class="sg-hs__breadcrumb-sep">/</span>';

    return '<nav class="sg-hs__breadcrumb" aria-label="Breadcrumb">'
         . implode( $separator, $trail )
         . '</nav>';
}

function sg_hero_split_render( $attr ) {

    $inner_style = 'max-width:' . esc_attr( $attr['innerWidth'] ) . ';margin:0 auto;';

    $title2_raw = isset( $attr['titleLine2'] ) ? $attr['titleLine2'] : '';
    $primary    = isset( $attr['primaryColor'] ) ? esc_attr( $attr['primaryColor'] ) : '#00c8ff';

    $title2 = preg_replace_callback(
        '/\{([^}]+)\}/',
        function ( $m ) use ( $primary ) {
            return '<span style="color:' . $primary . '">' . esc_html( $m[1] ) . '</span>';
        },
        $title2_raw
    );

    $icon_items = array();
    if ( ! empty( $attr['iconItems'] ) ) {
        $decoded = json_decode( $attr['iconItems'], true );
        if ( is_array( $decoded ) ) {
            $icon_items = $decoded;
        }
    }

    $stars_html = '';
    if ( ! empty( $attr['showStars'] ) && ! empty( $attr['starsText'] ) ) {
        $count      = intval( $attr['starsCount'] );
        $star_color = esc_attr( $attr['starsColor'] );
        $stars      = str_repeat( '<span style="color:' . $star_color . ';font-size:18px;">&#9733;</span>', $count );
        $stars_html = '<div class="sg-hs__stars">' . $stars
            . '<span class="sg-hs__stars-text">' . esc_html( $attr['starsText'] ) . '</span></div>';
    }

    ob_start();
    ?>
    <section class="sg-hs">

        <?php if ( ! empty( $attr['bgImage'] ) ) : ?>
        <div class="sg-hs__bg-image" style="background-image:url(<?php echo esc_url( $attr['bgImage'] ); ?>);"></div>
        <?php endif; ?>

        <div class="sg-hs__inner" style="<?php echo $inner_style; ?>">
            <div class="sg-hs__content">

                <?php if ( ! empty( $attr['showBreadcrumb'] ) ) : ?>
                <?php echo sg_hero_split_breadcrumb(); ?>
                <?php endif; ?>

                <?php if ( ! empty( $attr['titleLine1'] ) || ! empty( $title2_raw ) ) : ?>
                <h1 class="sg-hs__title">
                    <?php if ( ! empty( $attr['titleLine1'] ) ) : ?>
                    <span class="sg-hs__title-line1"><?php echo esc_html( $attr['titleLine1'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( ! empty( $title2_raw ) ) : ?>
                    <span class="sg-hs__title-line2"><?php echo wp_kses( $title2, array( 'span' => array( 'style' => array() ) ) ); ?></span>
                    <?php endif; ?>
                </h1>
                <?php endif; ?>

                <?php if ( ! empty( $attr['description'] ) ) : ?>
                <p class="sg-hs__desc"><?php echo esc_html( $attr['description'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $attr['showIconList'] ) && ! empty( $icon_items ) ) : ?>
                <div class="sg-hs__icon-list">
                    <?php foreach ( $icon_items as $item ) :
                        $label      = isset( $item['label'] )  ? $item['label']  : '';
                        $icon_url   = isset( $item['iconUrl'] ) ? $item['iconUrl'] : '';
                        $icon_color = isset( $item['color'] )  ? $item['color']  : $attr['primaryColor'];
                    ?>
                    <div class="sg-hs__icon-item">
                        <?php if ( $icon_url ) : ?>
                        <img src="<?php echo esc_url( $icon_url ); ?>" alt="" class="sg-hs__icon-img" />
                        <?php else : ?>
                        <span class="sg-hs__icon-check" style="color:<?php echo esc_attr( $icon_color ); ?>">&#10003;</span>
                        <?php endif; ?>
                        <span class="sg-hs__icon-label" style="color:<?php echo esc_attr( $icon_color ); ?>"><?php echo esc_html( $label ); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php
                $show_btns = ( ! empty( $attr['showBtn1'] ) && ! empty( $attr['btn1Text'] ) )
                          || ( ! empty( $attr['showBtn2'] ) && ! empty( $attr['btn2Text'] ) );
                ?>
                <?php if ( $show_btns ) : ?>
                <div class="sg-hs__buttons">
                    <?php if ( ! empty( $attr['showBtn1'] ) && ! empty( $attr['btn1Text'] ) ) : ?>
                    <a href="<?php echo esc_url( $attr['btn1Url'] ); ?>" class="sg-hs__btn sg-hs__btn--<?php echo esc_attr( $attr['btn1Style'] ); ?>"><?php echo esc_html( $attr['btn1Text'] ); ?></a>
                    <?php endif; ?>
                    <?php if ( ! empty( $attr['showBtn2'] ) && ! empty( $attr['btn2Text'] ) ) : ?>
                    <a href="<?php echo esc_url( $attr['btn2Url'] ); ?>" class="sg-hs__btn sg-hs__btn--<?php echo esc_attr( $attr['btn2Style'] ); ?>"><?php echo esc_html( $attr['btn2Text'] ); ?></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php echo $stars_html; ?>

            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
