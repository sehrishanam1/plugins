<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_Leads_DB {

    const TABLE = 'anexly_leads';

    /**
     * Run on plugin activation — creates the custom table.
     */
    public static function create_table() {
        global $wpdb;
        $table   = $wpdb->prefix . self::TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email       VARCHAR(191)        NOT NULL,
            source      VARCHAR(50)         NOT NULL DEFAULT 'newsletter',
            ip_address  VARCHAR(45)                  DEFAULT NULL,
            status      TINYINT(1)          NOT NULL DEFAULT 1,
            subscribed_at DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY   email (email)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'aleads_db_version', ALEADS_VERSION );
    }

    /**
     * Insert a new lead. Returns true on success, WP_Error on failure.
     *
     * @param string $email
     * @param string $source  'newsletter' | 'popup'
     * @return true|WP_Error
     */
    public static function insert( string $email, string $source = 'newsletter' ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        // Duplicate check
        $exists = $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s LIMIT 1", $email )
        );
        if ( $exists ) {
            return new WP_Error( 'duplicate_email', __( 'You are already subscribed!', 'anexly-leads' ) );
        }

        $inserted = $wpdb->insert(
            $table,
            [
                'email'      => $email,
                'source'     => $source,
                'ip_address' => self::get_ip(),
                'status'     => 1,
            ],
            [ '%s', '%s', '%s', '%d' ]
        );

        if ( false === $inserted ) {
            return new WP_Error( 'db_error', __( 'Could not save your email. Please try again.', 'anexly-leads' ) );
        }

        return true;
    }

    /**
     * Fetch all leads with optional filters.
     *
     * @param array $args  Keys: search, source, status, orderby, order, per_page, offset
     * @return array
     */
    public static function get_leads( array $args = [] ): array {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $defaults = [
            'search'   => '',
            'source'   => '',
            'status'   => '',
            'orderby'  => 'subscribed_at',
            'order'    => 'DESC',
            'per_page' => 25,
            'offset'   => 0,
        ];
        $args = wp_parse_args( $args, $defaults );

        $where  = 'WHERE 1=1';
        $params = [];

        if ( $args['search'] ) {
            $where   .= ' AND email LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }
        if ( $args['source'] ) {
            $where   .= ' AND source = %s';
            $params[] = $args['source'];
        }
        if ( $args['status'] !== '' ) {
            $where   .= ' AND status = %d';
            $params[] = (int) $args['status'];
        }

        $orderby  = in_array( $args['orderby'], [ 'id', 'email', 'source', 'subscribed_at' ], true )
                    ? $args['orderby'] : 'subscribed_at';
        $order    = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $per_page = max( 1, (int) $args['per_page'] );
        $offset   = max( 0, (int) $args['offset'] );

        $sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        if ( $params ) {
            $sql = $wpdb->prepare( $sql, ...$params );
        }

        return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
    }

    /**
     * Count leads with optional filters (same filters as get_leads minus pagination).
     */
    public static function count_leads( array $args = [] ): int {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $where  = 'WHERE 1=1';
        $params = [];

        if ( ! empty( $args['search'] ) ) {
            $where   .= ' AND email LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }
        if ( ! empty( $args['source'] ) ) {
            $where   .= ' AND source = %s';
            $params[] = $args['source'];
        }
        if ( isset( $args['status'] ) && $args['status'] !== '' ) {
            $where   .= ' AND status = %d';
            $params[] = (int) $args['status'];
        }

        $sql = "SELECT COUNT(*) FROM {$table} {$where}";
        if ( $params ) {
            $sql = $wpdb->prepare( $sql, ...$params );
        }

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Delete a lead by ID.
     */
    public static function delete( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->delete(
            $wpdb->prefix . self::TABLE,
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    /**
     * Export all leads as CSV string.
     */
    public static function export_csv(): string {
        $leads = self::get_leads( [ 'per_page' => 99999 ] );
        if ( empty( $leads ) ) return '';

        ob_start();
        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, [ 'ID', 'Email', 'Source', 'IP', 'Status', 'Subscribed At' ] );
        foreach ( $leads as $row ) {
            fputcsv( $out, [
                $row['id'],
                $row['email'],
                $row['source'],
                $row['ip_address'],
                $row['status'] ? 'Active' : 'Unsubscribed',
                $row['subscribed_at'],
            ]);
        }
        fclose( $out );
        return ob_get_clean();
    }

    /** Best-effort real IP */
    private static function get_ip(): string {
        foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ] as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                return sanitize_text_field( explode( ',', $_SERVER[ $key ] )[0] );
            }
        }
        return '';
    }
}
