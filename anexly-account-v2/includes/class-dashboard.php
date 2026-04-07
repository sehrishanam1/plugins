<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_MA_Dashboard {

    public static function render() {
        if ( ! is_user_logged_in() ) return '';

        $user       = wp_get_current_user();
        $first_name = $user->display_name ?: __( 'User', 'anexly-my-account' );

        $per_page = 6;
        $paged    = isset( $_GET['anx_page'] ) ? max(1, intval($_GET['anx_page'])) : 1;
        $offset   = ( $paged - 1 ) * $per_page;

        $total_orders_ids = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => -1,
            'return'   => 'ids',
        ]);
        $total_count = count( $total_orders_ids );

        $total_subs  = self::get_total_subscriptions();
        $total_saved = self::get_total_saved();
        $total_paid  = self::get_total_paid();

        $subs_change  = self::get_month_change( 'subscriptions' );
        $saved_change = self::get_month_change( 'saved' );
        $paid_change  = self::get_month_change( 'paid' );

        $orders = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => $per_page,
            'offset'   => $offset,
            'orderby'  => 'date',
            'order'    => 'DESC',
        ]);

        ob_start(); ?>
        <div class="anx-dashboard">
            <div class="anx-welcome">
                <h1>Welcome back, <?php echo esc_html( $first_name ); ?>👋</h1>
                <p>Here’s what’s happening with your subscriptions today.</p>
            </div>

            <div class="anx-stats-grid">
                <div class="anx-stat-card">
                    <div class="anx-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
<path d="M17.1073 2.90668C16.7599 2.74821 16.3825 2.6662 16.0006 2.6662C15.6188 2.6662 15.2414 2.74821 14.8939 2.90668L3.46727 8.10667C3.23067 8.211 3.02951 8.38187 2.88829 8.59848C2.74706 8.81509 2.67188 9.06809 2.67188 9.32667C2.67188 9.58525 2.74706 9.83826 2.88829 10.0549C3.02951 10.2715 3.23067 10.4423 3.46727 10.5467L14.9073 15.76C15.2547 15.9185 15.6321 16.0005 16.0139 16.0005C16.3958 16.0005 16.7732 15.9185 17.1206 15.76L28.5606 10.56C28.7972 10.4557 28.9984 10.2848 29.1396 10.0682C29.2808 9.85159 29.356 9.59859 29.356 9.34001C29.356 9.08143 29.2808 8.82843 29.1396 8.61182C28.9984 8.39521 28.7972 8.22433 28.5606 8.12001L17.1073 2.90668Z" stroke="white" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M29.3346 23.5333L17.108 29.08C16.7606 29.2385 16.3832 29.3205 16.0013 29.3205C15.6194 29.3205 15.2421 29.2385 14.8946 29.08L2.66797 23.5333" stroke="white" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M29.3346 16.8672L17.108 22.4139C16.7606 22.5723 16.3832 22.6543 16.0013 22.6543C15.6194 22.6543 15.2421 22.5723 14.8946 22.4139L2.66797 16.8672" stroke="white" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                    </div>
                    <div class="anx-stat-value"><?php echo esc_html( $total_subs ); ?></div>
                    <div class="anx-stat-label">Total subscriptions</div>
                    <div class="anx-stat-change <?php echo $subs_change >= 0 ? 'positive' : 'negative'; ?>">
                        +<?php echo abs($subs_change); ?> this month
                    </div>
                </div>

                <div class="anx-stat-card">
                    <div class="anx-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
<path d="M27.946 15.444C27.982 15.7894 28 16.1527 28 16.534C28 19.128 27.096 20.872 26.22 21.964C25.8664 22.4075 25.4615 22.8076 25.014 23.156C24.8619 23.2715 24.7044 23.3796 24.542 23.48L24.524 23.49C24.3654 23.5758 24.233 23.7029 24.1408 23.8578C24.0485 24.0128 23.9999 24.1897 24 24.37V27C24 27.2652 23.8946 27.5196 23.7071 27.7071C23.5196 27.8947 23.2652 28 23 28H22C22 27.4696 21.7893 26.9609 21.4142 26.5858C21.0391 26.2107 20.5304 26 20 26H16C15.4696 26 14.9609 26.2107 14.5858 26.5858C14.2107 26.9609 14 27.4696 14 28H13C12.7348 28 12.4804 27.8947 12.2929 27.7071C12.1054 27.5196 12 27.2652 12 27V25.794C12.0003 25.5459 11.9083 25.3066 11.742 25.1226C11.5756 24.9385 11.3468 24.8228 11.1 24.798L10.908 24.754C9.82323 24.4661 8.83469 23.8945 8.044 23.098C7.16 22.198 6.618 21.014 6.328 20.192C6.04 19.374 5.384 18.634 4.448 18.37C4.31977 18.333 4.20691 18.2556 4.12619 18.1493C4.04548 18.043 4.00122 17.9135 4 17.78V16.192C3.99989 16.066 4.04082 15.9434 4.1166 15.8427C4.19238 15.742 4.29888 15.6688 4.42 15.634C5.39 15.356 6.038 14.572 6.312 13.744C6.536 13.064 6.932 12.192 7.576 11.538C8.31336 10.801 9.16586 10.189 10.1 9.72602C10.18 9.68602 10.24 9.65735 10.28 9.64002L10.32 9.62002L10.328 9.61802C10.508 9.54086 10.6614 9.41254 10.7691 9.24899C10.8768 9.08544 10.9342 8.89386 10.934 8.69802V5.35002C11.3782 5.73024 11.8458 6.08226 12.334 6.40402C12.968 6.82002 13.708 7.21402 14.482 7.42402C14.71 6.76802 15.022 6.14402 15.41 5.56802H15.398C14.84 5.51002 14.158 5.20802 13.428 4.73002C12.7589 4.28042 12.1299 3.77387 11.548 3.21602C11.331 3.01113 11.0592 2.8737 10.7655 2.82044C10.4719 2.76718 10.1691 2.80038 9.894 2.91602C9.61162 3.02792 9.36924 3.22187 9.19813 3.47283C9.02701 3.7238 8.93502 4.02027 8.934 4.32402V8.07802C7.91294 8.62555 6.97655 9.31807 6.154 10.134C5.2 11.098 4.682 12.302 4.414 13.116C4.302 13.45 4.078 13.652 3.87 13.71C3.33126 13.8642 2.85737 14.1897 2.51999 14.6371C2.18261 15.0845 2.00008 15.6296 2 16.19V17.78C2 18.95 2.778 19.976 3.902 20.294C4.0299 20.3386 4.14548 20.4127 4.23932 20.5103C4.33316 20.608 4.4026 20.7264 4.442 20.856C4.774 21.8 5.438 23.302 6.622 24.502C7.56468 25.4547 8.72355 26.1655 10 26.574V27C10 27.7957 10.3161 28.5587 10.8787 29.1213C11.4413 29.6839 12.2044 30 13 30H14C14.5304 30 15.0391 29.7893 15.4142 29.4142C15.7893 29.0392 16 28.5305 16 28H20C20 28.5305 20.2107 29.0392 20.5858 29.4142C20.9609 29.7893 21.4696 30 22 30H23C23.7956 30 24.5587 29.6839 25.1213 29.1213C25.6839 28.5587 26 27.7957 26 27V24.914C26.0733 24.862 26.152 24.8034 26.236 24.738C26.809 24.2927 27.3272 23.7811 27.78 23.214C28.904 21.814 30 19.638 30 16.534C29.9973 15.2367 29.8173 14.0687 29.46 13.03C29.1039 13.9188 28.5911 14.7364 27.946 15.444ZM27.582 7.75002C28.0823 8.95646 28.1752 10.2932 27.8468 11.5573C27.5183 12.8214 26.7863 13.9437 25.762 14.754C24.928 15.412 23.786 15.362 22.806 14.954L18.026 12.976C17.046 12.57 16.202 11.798 16.078 10.744C15.9119 9.30643 16.2709 7.85725 17.0887 6.66335C17.9065 5.46945 19.1282 4.61123 20.5286 4.24675C21.9291 3.88226 23.4142 4.03604 24.7102 4.67978C26.0063 5.32352 27.0262 6.41387 27.582 7.75002ZM24.522 13.184C25.0694 12.7515 25.4942 12.1833 25.7543 11.5359C26.0144 10.8886 26.1007 10.1844 26.0047 9.49338C25.9087 8.80235 25.6337 8.14841 25.207 7.59643C24.7804 7.04445 24.2168 6.6136 23.5722 6.34663C22.9276 6.07965 22.2245 5.98582 21.5324 6.07444C20.8404 6.16306 20.1836 6.43106 19.6271 6.85184C19.0706 7.27262 18.6338 7.83158 18.3599 8.47327C18.0861 9.11496 17.9848 9.81709 18.066 10.51C18.07 10.55 18.086 10.624 18.198 10.742C18.3685 10.9088 18.5704 11.04 18.792 11.128L23.572 13.108C23.7895 13.208 24.0267 13.2579 24.266 13.254C24.426 13.25 24.492 13.208 24.522 13.184ZM11.5 15C11.8978 15 12.2794 14.842 12.5607 14.5607C12.842 14.2794 13 13.8978 13 13.5C13 13.1022 12.842 12.7207 12.5607 12.4394C12.2794 12.1581 11.8978 12 11.5 12C11.1022 12 10.7206 12.1581 10.4393 12.4394C10.158 12.7207 10 13.1022 10 13.5C10 13.8978 10.158 14.2794 10.4393 14.5607C10.7206 14.842 11.1022 15 11.5 15Z" fill="white"/>
</svg>
                    </div>
                    <div class="anx-stat-value"><?php echo wc_price( $total_saved ); ?></div>
                    <div class="anx-stat-label">Total saved</div>
                    <div class="anx-stat-change <?php echo $saved_change >= 0 ? 'positive' : 'negative'; ?>">
                        <?php if ( $saved_change > 0 ) : ?>
                            +<?php echo wc_price( $saved_change ); ?> this month
                        <?php elseif ( $saved_change < 0 ) : ?>
                            ↓ <?php echo wc_price( $saved_change ); ?> this month
                        <?php else : ?>
                            No discounts this month
                        <?php endif; ?>
                    </div>
                </div>

                <div class="anx-stat-card">
                    <div class="anx-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
<path d="M16 2.66669V29.3334M21.3333 6.66669H13.3333C12.0957 6.66669 10.9087 7.15835 10.0335 8.03352C9.15833 8.90869 8.66667 10.0957 8.66667 11.3334C8.66667 12.571 9.15833 13.758 10.0335 14.6332C10.9087 15.5084 12.0957 16 13.3333 16H19.3333C20.571 16 21.758 16.4917 22.6332 17.3669C23.5083 18.242 24 19.429 24 20.6667C24 21.9044 23.5083 23.0913 22.6332 23.9665C21.758 24.8417 20.571 25.3334 19.3333 25.3334H8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                    </div>
                    <div class="anx-stat-value"><?php echo wc_price( $total_paid ); ?></div>
                    <div class="anx-stat-label">Total Paid</div>
                    <div class="anx-stat-change <?php echo $paid_change >= 0 ? 'positive' : 'negative'; ?>">
                        <?php if ( $paid_change > 0 ) : ?>
                            +<?php echo wc_price( $paid_change ); ?> this month
                        <?php else : ?>
                            <?php echo wc_price(0); ?> this month
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="anx-section anx-dashboard-orders">
                <h2 class="anx-section-title">Recent orders</h2>
                <div class="anx-table-wrap">
                    <table class="anx-table">
                        <thead>
                            <tr>
                                <th>ORDERD</th>
                                <th>DATE</th>
                                <th>PRODUCTS</th>
                                <th>STATUS</th>
                                <th>TOTAL</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $orders as $order ) :
                                $status = $order->get_status();
                                $items  = $order->get_items();
                                $names  = [];
                                foreach ( $items as $item ) $names[] = $item->get_name();
                            ?>
                            <tr>
                                <td>ORD<?php echo esc_html( $order->get_order_number() ); ?></td>
                                <td><?php echo esc_html( $order->get_date_created()->date('d M Y') ); ?></td>
                                <td><?php echo esc_html( implode(', ', $names) ); ?></td>
                                <td><span class="anx-badge anx-badge-<?php echo esc_attr($status); ?>"><?php echo esc_html( ucfirst($status) ); ?></span></td>
                                <td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                                <td><a href="<?php echo esc_url( add_query_arg(['anx_tab' => 'orders'], get_permalink()) ); ?>" class="anx-link-btn">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php self::render_pagination( $per_page, $paged, $total_count ); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function get_total_subscriptions() {
        $orders = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => -1,
            'return'   => 'ids',
        ]);
        return count( $orders );
    }

    /**
     * Sum of all discount amounts across the user's orders.
     * Includes coupon discounts + any order-level discounts WooCommerce records.
     */
    private static function get_total_saved() {
        $total_discount = 0;
        $orders = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => -1,
            'status'   => [ 'completed', 'processing', 'on-hold', 'pending' ],
        ]);
        foreach ( $orders as $order ) {
            // get_discount_total() returns the pre-tax discount amount
            $total_discount += (float) $order->get_discount_total();
        }
        return $total_discount;
    }

    private static function get_total_paid() {
        $total  = 0;
        $orders = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => -1,
            'status'   => [ 'completed', 'processing' ],
        ]);
        foreach ( $orders as $o ) $total += $o->get_total();
        return $total;
    }

    /**
     * Returns the change value for this month vs last month.
     * For 'saved' and 'paid': returns the this-month amount (not a percentage).
     * For 'subscriptions': returns count of orders placed this month.
     */
    private static function get_month_change( $type ) {
        $user_id    = get_current_user_id();
        $month_start = date( 'Y-m-01 00:00:00' );
        $month_end   = date( 'Y-m-t 23:59:59' );

        $month_orders = wc_get_orders([
            'customer'   => $user_id,
            'limit'      => -1,
            'date_after' => $month_start,
            'date_before'=> $month_end,
            'status'     => [ 'completed', 'processing', 'on-hold', 'pending' ],
        ]);

        if ( $type === 'subscriptions' ) {
            return count( $month_orders );
        }

        if ( $type === 'saved' ) {
            $month_discount = 0;
            foreach ( $month_orders as $o ) {
                $month_discount += (float) $o->get_discount_total();
            }
            return round( $month_discount, 2 );
        }

        if ( $type === 'paid' ) {
            $month_paid = 0;
            foreach ( $month_orders as $o ) {
                $month_paid += (float) $o->get_total();
            }
            return round( $month_paid, 2 );
        }

        return 0;
    }

    private static function render_pagination( $per_page, $current, $total_count ) {
        $total_pages = max( 1, (int) ceil( $total_count / $per_page ) );
        if ( $total_pages <= 1 ) return;

        $base = remove_query_arg( 'anx_page' );
        ?>
        <div class="anx-pagination">
            <div class="anx-pagination-info">
                Showing <?php echo esc_html( ( ( $current - 1 ) * $per_page ) + 1 ); ?>-<?php echo esc_html( min($current * $per_page, $total_count) ); ?> of <?php echo esc_html( $total_count ); ?> entries
            </div>
            <div class="anx-pagination-controls">
                <a class="anx-page-btn <?php echo $current <= 1 ? 'is-disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'anx_page', max(1, $current - 1), $base ) ); ?>">‹ <span>Previous</span></a>
                <?php for ( $i = 1; $i <= min(3, $total_pages); $i++ ) : ?>
                    <a class="anx-page-btn <?php echo $i === $current ? 'active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'anx_page', $i, $base ) ); ?>"><?php echo esc_html( $i ); ?></a>
                <?php endfor; ?>
                <?php if ( $total_pages > 4 ) : ?>
                    <span class="anx-page-dots">...</span>
                    <a class="anx-page-btn" href="<?php echo esc_url( add_query_arg( 'anx_page', $total_pages, $base ) ); ?>"><?php echo esc_html( $total_pages ); ?></a>
                <?php endif; ?>
                <a class="anx-page-btn <?php echo $current >= $total_pages ? 'is-disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'anx_page', min($total_pages, $current + 1), $base ) ); ?>"><span>Next</span> ›</a>
            </div>
        </div>
        <?php
    }
}