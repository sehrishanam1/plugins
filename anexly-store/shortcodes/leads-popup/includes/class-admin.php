<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_Leads_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    public function register_menu() {
        add_submenu_page(
            'anexly-account', 
            __( 'Anexly Leads', 'anexly-leads' ),
            __( 'Anexly Leads', 'anexly-leads' ),
            'manage_options',
            'anexly-leads',
            [ $this, 'render_page' ]
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_anexly-leads' !== $hook ) return;
        wp_enqueue_style(
            'anexly-leads-admin',
            ALEADS_URL . 'assets/admin.css',
            [],
            ALEADS_VERSION
        );
        wp_enqueue_script(
            'anexly-leads-admin',
            ALEADS_URL . 'assets/frontend.js',
            [ 'jquery' ],
            ALEADS_VERSION,
            true
        );
        wp_localize_script( 'anexly-leads-admin', 'AnexlyLeads', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'anexly_admin_nonce' ),
        ]);
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        // Filters
        $search   = isset( $_GET['s'] )      ? sanitize_text_field( wp_unslash( $_GET['s'] ) )  : '';
        $source   = isset( $_GET['source'] ) ? sanitize_key( wp_unslash( $_GET['source'] ) )    : '';
        $paged    = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );
        $per_page = 25;

        $args = [
            'search'   => $search,
            'source'   => $source,
            'per_page' => $per_page,
            'offset'   => ( $paged - 1 ) * $per_page,
        ];

        $leads = Anexly_Leads_DB::get_leads( $args );
        $total = Anexly_Leads_DB::count_leads( $args );
        $pages = (int) ceil( $total / $per_page );

        $export_url = wp_nonce_url(
            admin_url( 'admin-ajax.php?action=anexly_export_csv' ),
            'anexly_export_csv'
        );
        ?>
        <div class="wrap anexly-admin-wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Anexly Leads', 'anexly-leads' ); ?></h1>
            <span class="anexly-total-badge"><?php printf( _n( '%s subscriber', '%s subscribers', $total, 'anexly-leads' ), number_format_i18n( $total ) ); ?></span>

            <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php _e( '⬇ Export CSV', 'anexly-leads' ); ?></a>
            <hr class="wp-header-end">

            <!-- Filters -->
            <form method="get" class="anexly-filter-form">
                <input type="hidden" name="page" value="anexly-leads" />
                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search email…', 'anexly-leads' ); ?>" class="regular-text" />
                <select name="source">
                    <option value=""><?php _e( 'All Sources', 'anexly-leads' ); ?></option>
                    <option value="newsletter" <?php selected( $source, 'newsletter' ); ?>><?php _e( 'Newsletter Form', 'anexly-leads' ); ?></option>
                    <option value="popup" <?php selected( $source, 'popup' ); ?>><?php _e( 'Popup', 'anexly-leads' ); ?></option>
                </select>
                <?php submit_button( __( 'Filter', 'anexly-leads' ), 'secondary', '', false ); ?>
                <?php if ( $search || $source ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=anexly-leads' ) ); ?>" class="button"><?php _e( 'Reset', 'anexly-leads' ); ?></a>
                <?php endif; ?>
            </form>

            <!-- Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( '#', 'anexly-leads' ); ?></th>
                        <th><?php _e( 'Email', 'anexly-leads' ); ?></th>
                        <th><?php _e( 'Source', 'anexly-leads' ); ?></th>
                        <th><?php _e( 'IP Address', 'anexly-leads' ); ?></th>
                        <th><?php _e( 'Subscribed At', 'anexly-leads' ); ?></th>
                        <th><?php _e( 'Actions', 'anexly-leads' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $leads ) ) : ?>
                    <tr><td colspan="6" style="text-align:center;padding:30px;"><?php _e( 'No leads found.', 'anexly-leads' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $leads as $lead ) : ?>
                    <tr id="anexly-lead-<?php echo (int) $lead['id']; ?>">
                        <td><?php echo (int) $lead['id']; ?></td>
                        <td><strong><?php echo esc_html( $lead['email'] ); ?></strong></td>
                        <td>
                            <span class="anexly-source-badge anexly-source-<?php echo esc_attr( $lead['source'] ); ?>">
                                <?php echo esc_html( ucfirst( $lead['source'] ) ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( $lead['ip_address'] ?: '—' ); ?></td>
                        <td><?php echo esc_html( wp_date( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $lead['subscribed_at'] ) ) ); ?></td>
                        <td>
                            <button
                                class="button button-small anexly-delete-lead"
                                data-id="<?php echo (int) $lead['id']; ?>"
                                data-email="<?php echo esc_attr( $lead['email'] ); ?>"
                            ><?php _e( 'Delete', 'anexly-leads' ); ?></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ( $pages > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links([
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $pages,
                        'current'   => $paged,
                    ]);
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Shortcode Reference -->
            <div class="anexly-shortcode-ref">
                <h3><?php _e( 'Shortcode Reference', 'anexly-leads' ); ?></h3>
                <table class="widefat" style="max-width:700px">
                    <tr>
                        <td><code>[anexly_newsletter]</code></td>
                        <td><?php _e( 'Renders the inline newsletter signup form (exact match to design).', 'anexly-leads' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>[anexly_newsletter button_text="Subscribe" show_consent="no"]</code></td>
                        <td><?php _e( 'Custom button text, no consent checkbox.', 'anexly-leads' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>[anexly_popup]</code></td>
                        <td><?php _e( 'Force the popup on any page/post. For logged-out users it fires randomly after 5-15 seconds.', 'anexly-leads' ); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}

new Anexly_Leads_Admin();