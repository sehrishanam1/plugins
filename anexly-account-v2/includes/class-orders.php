<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_MA_Orders {

    public static function render() {
        if ( ! is_user_logged_in() ) return '';
        $per_page    = 6;
        $paged       = isset( $_GET['anx-creds-closeanx_page'] ) ? max(1, intval($_GET['anx_page'])) : 1;
        $offset      = ( $paged - 1 ) * $per_page;

        $all_orders = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => -1,
            'return'   => 'ids',
        ]);
        $total_count = count( $all_orders );
        $total_pages = max( 1, (int) ceil( $total_count / $per_page ) );

        $orders = wc_get_orders([
            'customer' => get_current_user_id(),
            'limit'    => $per_page,
            'offset'   => $offset,
            'orderby'  => 'date',
            'order'    => 'DESC',
        ]);

        ob_start(); ?>
        <div class="anx-orders">
            <div class="anx-welcome">
                <h1>My Orders</h1>
                <p>View and manage your purchases</p>
            </div>

            <div class="anx-section anx-orders-panel">
                <div class="anx-table-wrap">
                    <table class="anx-table anx-orders-table">
                        <thead>
                            <tr>
                                <th>ORDER</th>
                                <th>DATE</th>
                                <th>PRODUCTS</th>
                                <th>STATUS</th>
                                <th>TOTAL</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $orders as $index => $order ) :
                                $status   = $order->get_status();
                                $items    = $order->get_items();
                                $names    = [];
                                foreach ( $items as $item ) $names[] = $item->get_name();
                                $order_id = $order->get_id();
                                $is_open  = false; // all rows closed by default
                            ?>

                            <!-- ── Summary row ───────────────────────────────────── -->
                            <tr class="anx-order-row <?php echo $is_open ? 'anx-row-expanded' : ''; ?>"
                                data-order-id="<?php echo esc_attr($order_id); ?>">

                                <td><?php echo esc_html( $order->get_order_number() ); ?></td>
                                <td><?php echo esc_html( $order->get_date_created()->date('d M Y') ); ?></td>
                                <td><?php echo esc_html( implode(', ', $names) ); ?></td>
                                <td>
                                    <span class="anx-badge anx-badge-<?php echo esc_attr($status); ?>">
                                        <?php echo esc_html( ucfirst($status) ); ?>
                                    </span>
                                </td>
                                <td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                                <td>
                                    <button class="anx-toggle-btn <?php echo $is_open ? 'open' : ''; ?>"
                                            data-order="<?php echo esc_attr($order_id); ?>"
                                            type="button">
                                        <span class="anx-toggle-icon">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M7 10l5 5 5-5"
                                                      stroke="currentColor"
                                                      stroke-width="2"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </button>
                                </td>
                            </tr>

                            <!-- ── Detail row (sibling <tr>, always in the DOM) ─── -->
                            <tr class="anx-order-detail-row <?php echo $is_open ? 'anx-detail-open' : ''; ?>"
                                id="anx-detail-<?php echo esc_attr($order_id); ?>"
                                style="<?php echo $is_open ? '' : 'display:none;'; ?>">

                                <td colspan="6" class="anx-detail-td">
                                    <div class="anx-order-detail">

                                        <?php foreach ( $items as $item ) :
                                            $product = $item->get_product();
                                            $img     = $product ? get_the_post_thumbnail_url( $product->get_id(), 'thumbnail' ) : '';
                                        ?>
                                        <div class="anx-detail-product">
                                            <div class="anx-detail-product-inner">

                                                <div class="anx-detail-leftgroup">
                                                    <?php if ( $img ) : ?>
                                                        <img src="<?php echo esc_url($img); ?>"
                                                             alt="<?php echo esc_attr($item->get_name()); ?>"
                                                             class="anx-product-thumb">
                                                    <?php else : ?>
                                                        <div class="anx-product-thumb-placeholder">
                                                            <svg viewBox="0 0 66 66" fill="none">
                                                                <rect width="66" height="66" rx="10" fill="#3448F0"/>
                                                                <path d="M14 38c3-10 5-14 7-14 2 0 3 5 4 15 1-6 2-9 4-9 2 0 3 7 4 16 1-9 2-14 4-14 2 0 3 5 4 15l2-8c1-4 2-6 4-6 1 0 2 2 3 5l3 8"
                                                                      stroke="#fff" stroke-width="4"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="anx-detail-info">
                                                        <div class="anx-detail-name"><?php echo esc_html( $item->get_name() ); ?></div>
                                                        <?php
                                                        $variation = $item->get_variation_id() ? wc_get_product($item->get_variation_id()) : null;
                                                        $duration  = $variation ? $variation->get_attribute('duration') : '1 month';
                                                        ?>
                                                        <div class="anx-detail-duration"><?php echo esc_html($duration ?: '1 month'); ?></div>
                                                        <div class="anx-detail-price"><?php echo wc_price( $item->get_total() ); ?></div>
                                                    </div>
                                                </div><!-- /.anx-detail-leftgroup -->

                                                <div class="anx-detail-right">
                                                    <div class="anx-detail-qty">Quantity: <strong><?php echo esc_html( $item->get_quantity() ); ?></strong></div>
                                                    <div class="anx-detail-billing">Billing Cycle: <strong>One-time purchase</strong></div>
                                                </div>

                                                <div class="anx-detail-purchased">
                                                    Purchased: <strong><?php echo esc_html( $order->get_date_created()->date('M d, Y') ); ?></strong>
                                                </div>

                                            </div><!-- /.anx-detail-product-inner -->

                                            <?php $credentials = $order->get_meta('_anexly_credentials'); ?>
                                            <div class="anx-detail-actions">
                                                <button
                                                    class="anx-btn anx-btn-primary anx-show-creds"
                                                    data-creds="<?php echo esc_attr( base64_encode( wp_json_encode( [ 'raw' => (string) $credentials ] ) ) ); ?>"
                                                    type="button">
                                                    Show credentials
                                                </button>
                                            </div>

                                        </div><!-- /.anx-detail-product -->
                                        <?php endforeach; ?>

                                    </div><!-- /.anx-order-detail -->
                                </td>
                            </tr>
                            <!-- ── / Detail row ──────────────────────────────────── -->

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div><!-- /.anx-table-wrap -->

                <?php if ( $total_pages > 1 ) :
                    $base = remove_query_arg( 'anx_page' ); ?>
                <div class="anx-pagination">
                    <div class="anx-pagination-info">
                        Showing <?php echo esc_html( $offset + 1 ); ?>-<?php echo esc_html( min($offset + $per_page, $total_count) ); ?> of <?php echo esc_html( $total_count ); ?> entries
                    </div>
                    <div class="anx-pagination-controls">
                        <a class="anx-page-btn <?php echo $paged <= 1 ? 'is-disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'anx_page', max(1, $paged - 1), $base ) ); ?>">&#8249; <span>Previous</span></a>
                        <?php for ( $i = 1; $i <= min(3, $total_pages); $i++ ) : ?>
                            <a class="anx-page-btn <?php echo $i === $paged ? 'active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'anx_page', $i, $base ) ); ?>"><?php echo esc_html( $i ); ?></a>
                        <?php endfor; ?>
                        <?php if ( $total_pages > 4 ) : ?>
                            <span class="anx-page-dots">...</span>
                            <a class="anx-page-btn" href="<?php echo esc_url( add_query_arg( 'anx_page', $total_pages, $base ) ); ?>"><?php echo esc_html( $total_pages ); ?></a>
                        <?php endif; ?>
                        <a class="anx-page-btn <?php echo $paged >= $total_pages ? 'is-disabled' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'anx_page', min($total_pages, $paged + 1), $base ) ); ?>"><span>Next</span> &#8250;</a>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.anx-orders-panel -->
        </div><!-- /.anx-orders -->

        <!-- ── Credentials modal ────────────────────────────────────── -->
        <div class="anx-creds-modal" id="anx-creds-modal" style="display:none;">
            <div class="anx-creds-backdrop"></div>

            <div class="anx-creds-dialog" role="dialog" aria-modal="true" aria-labelledby="anx-creds-title">
                <button type="button" class="anx-creds-close" aria-label="Close popup">×</button>

                <div class="anx-creds-icon-wrap">
                    <div class="anx-creds-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="39" height="44" viewBox="0 0 39 44" fill="none">
<path fill-rule="evenodd" clip-rule="evenodd" d="M6.5 13C6.5 9.55219 7.86964 6.24558 10.3076 3.80761C12.7456 1.36964 16.0522 0 19.5 0C22.9478 0 26.2544 1.36964 28.6924 3.80761C31.1304 6.24558 32.5 9.55219 32.5 13H34.6667C35.8159 13 36.9181 13.4565 37.7308 14.2692C38.5435 15.0819 39 16.1841 39 17.3333V39C39 40.1493 38.5435 41.2515 37.7308 42.0641C36.9181 42.8768 35.8159 43.3333 34.6667 43.3333H4.33333C3.18406 43.3333 2.08186 42.8768 1.2692 42.0641C0.456546 41.2515 0 40.1493 0 39V17.3333C0 16.1841 0.456546 15.0819 1.2692 14.2692C2.08186 13.4565 3.18406 13 4.33333 13H6.5ZM19.5 4.33333C21.7985 4.33333 24.0029 5.24643 25.6283 6.87174C27.2536 8.49706 28.1667 10.7015 28.1667 13H10.8333C10.8333 10.7015 11.7464 8.49706 13.3717 6.87174C14.9971 5.24643 17.2015 4.33333 19.5 4.33333ZM23.8333 26C23.8333 26.7606 23.6331 27.5079 23.2528 28.1666C22.8724 28.8253 22.3254 29.3723 21.6667 29.7527V32.5C21.6667 33.0746 21.4384 33.6257 21.0321 34.0321C20.6257 34.4384 20.0746 34.6667 19.5 34.6667C18.9254 34.6667 18.3743 34.4384 17.9679 34.0321C17.5616 33.6257 17.3333 33.0746 17.3333 32.5V29.7527C16.5073 29.2757 15.8617 28.5395 15.4967 27.6582C15.1317 26.7769 15.0676 25.7998 15.3145 24.8784C15.5614 23.9571 16.1054 23.1429 16.8622 22.5622C17.6189 21.9815 18.5461 21.6667 19.5 21.6667C20.6493 21.6667 21.7515 22.1232 22.5641 22.9359C23.3768 23.7485 23.8333 24.8507 23.8333 26Z" fill="#111013"/>
</svg>
                    </div>
                    <h3 class="anx-creds-title" id="anx-creds-title">Access Credentials</h3>
                </div>

                <div class="anx-creds-field">
                    <label>User Name:</label>
                    <div class="anx-creds-input-wrap">
                        <input type="text" id="anx-creds-username" readonly value="">
                        <button type="button" class="anx-creds-copy" data-copy-target="#anx-creds-username" aria-label="Copy username">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M5.83203 8.056C5.83203 7.46655 6.06619 6.90125 6.48299 6.48445C6.89979 6.06765 7.46509 5.8335 8.05453 5.8335H15.2762C15.5681 5.8335 15.8571 5.89098 16.1267 6.00267C16.3964 6.11437 16.6414 6.27807 16.8477 6.48445C17.0541 6.69083 17.2178 6.93584 17.3295 7.20548C17.4412 7.47513 17.4987 7.76413 17.4987 8.056V15.2777C17.4987 15.5695 17.4412 15.8585 17.3295 16.1282C17.2178 16.3978 17.0541 16.6428 16.8477 16.8492C16.6414 17.0556 16.3964 17.2193 16.1267 17.331C15.8571 17.4427 15.5681 17.5002 15.2762 17.5002H8.05453C7.76267 17.5002 7.47366 17.4427 7.20402 17.331C6.93437 17.2193 6.68936 17.0556 6.48299 16.8492C6.27661 16.6428 6.1129 16.3978 6.00121 16.1282C5.88952 15.8585 5.83203 15.5695 5.83203 15.2777V8.056Z" stroke="#737175" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.34333 13.9475C3.0875 13.8021 2.87471 13.5916 2.72658 13.3374C2.57846 13.0832 2.50028 12.7942 2.5 12.5V4.16667C2.5 3.25 3.25 2.5 4.16667 2.5H12.5C13.125 2.5 13.465 2.82083 13.75 3.33333" stroke="#737175" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        </button>
                    </div>
                </div>

                <div class="anx-creds-field">
                    <label>Password:</label>
                    <div class="anx-creds-input-wrap">
                        <input type="text" id="anx-creds-password" readonly value="">
                        <button type="button" class="anx-creds-copy" data-copy-target="#anx-creds-password" aria-label="Copy password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M5.83203 8.056C5.83203 7.46655 6.06619 6.90125 6.48299 6.48445C6.89979 6.06765 7.46509 5.8335 8.05453 5.8335H15.2762C15.5681 5.8335 15.8571 5.89098 16.1267 6.00267C16.3964 6.11437 16.6414 6.27807 16.8477 6.48445C17.0541 6.69083 17.2178 6.93584 17.3295 7.20548C17.4412 7.47513 17.4987 7.76413 17.4987 8.056V15.2777C17.4987 15.5695 17.4412 15.8585 17.3295 16.1282C17.2178 16.3978 17.0541 16.6428 16.8477 16.8492C16.6414 17.0556 16.3964 17.2193 16.1267 17.331C15.8571 17.4427 15.5681 17.5002 15.2762 17.5002H8.05453C7.76267 17.5002 7.47366 17.4427 7.20402 17.331C6.93437 17.2193 6.68936 17.0556 6.48299 16.8492C6.27661 16.6428 6.1129 16.3978 6.00121 16.1282C5.88952 15.8585 5.83203 15.5695 5.83203 15.2777V8.056Z" stroke="#737175" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3.34333 13.9475C3.0875 13.8021 2.87471 13.5916 2.72658 13.3374C2.57846 13.0832 2.50028 12.7942 2.5 12.5V4.16667C2.5 3.25 3.25 2.5 4.16667 2.5H12.5C13.125 2.5 13.465 2.82083 13.75 3.33333" stroke="#737175" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ── / Credentials modal ──────────────────────────────────── -->

        <?php
        return ob_get_clean();
    }
}