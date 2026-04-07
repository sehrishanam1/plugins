<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_Leads_Ajax {

    public function __construct() {
        // Both logged-in and logged-out users can subscribe
        add_action( 'wp_ajax_anexly_subscribe',        [ $this, 'handle_subscribe' ] );
        add_action( 'wp_ajax_nopriv_anexly_subscribe', [ $this, 'handle_subscribe' ] );

        // Admin: delete a lead
        add_action( 'wp_ajax_anexly_delete_lead', [ $this, 'handle_delete' ] );

        // Admin: export CSV
        add_action( 'wp_ajax_anexly_export_csv', [ $this, 'handle_export' ] );
    }

    public function handle_subscribe() {
        // Verify nonce
        if ( ! check_ajax_referer( 'anexly_subscribe_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed. Please refresh the page.', 'anexly-leads' ) ], 403 );
        }

        $email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : 'newsletter';

        if ( ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'anexly-leads' ) ] );
        }

        // Validate source
        if ( ! in_array( $source, [ 'newsletter', 'popup' ], true ) ) {
            $source = 'newsletter';
        }

        $result = Anexly_Leads_DB::insert( $email, $source );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [ 'message' => __( 'Thanks for subscribing! 🎉', 'anexly-leads' ) ] );
    }

    public function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
        }
        check_ajax_referer( 'anexly_admin_nonce', 'nonce' );

        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'Invalid ID' ] );
        }

        Anexly_Leads_DB::delete( $id );
        wp_send_json_success();
    }

    public function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        check_admin_referer( 'anexly_export_csv' );

        $csv = Anexly_Leads_DB::export_csv();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="anexly-leads-' . date('Y-m-d') . '.csv"' );
        header( 'Pragma: no-cache' );
        echo $csv;
        exit;
    }
}

new Anexly_Leads_Ajax();
