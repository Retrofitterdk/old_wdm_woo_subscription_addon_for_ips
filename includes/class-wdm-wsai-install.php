<?php
if (! class_exists('WdmWSAIInstall')) {
    class WdmWSAIInstall
    {

    /*
     * Creates all tables required for the plugin. It creates one
     * tables in the database. wsai_ip_mapping stores the  IP mapping of Subscription with order
     */

        public static function createTables()
        {
            global $wpdb;
            $wpdb->hide_errors();

            $collate = '';

            if ($wpdb->has_cap('collation')) {
                if (! empty($wpdb->charset)) {
                    $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
                }
                if (! empty($wpdb->collate)) {
                    $collate .= " COLLATE $wpdb->collate";
                }
            }

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $wdm_wsai_ip_mapping    = $wpdb->prefix . 'wsai_ip_mapping';
            error_log('table name'.$wdm_wsai_ip_mapping);
            $ip_mapping_table = "
                        CREATE TABLE IF NOT EXISTS {$wdm_wsai_ip_mapping} (
                                id bigint(20) NOT NULL AUTO_INCREMENT,
                                post_id bigint(20),
                                ip_address varchar(60),
                                UNIQUE KEY unique_mapping_table (post_id,ip_address),
                                PRIMARY KEY  (id)
                        ) $collate;
                        ";

            //print_r($ip_mapping_table);
           // die();

            @dbDelta($ip_mapping_table);

        }
    }
}
