<?php
/**
 * Plugin Name: WDM WooCommerce Subscription Addon For IP's
 * Plugin URI: http://wisdmlabs.com
 * Description: This extension plugin allows you to manage IP's in subscription product
 * Author: WisdmLabs
 * Version: 1.0.0
 * Author URI: http://wisdmlabs.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

include('includes/class-wdm-wsai-install.php');
include('includes/class-page-templater.php');

register_activation_hook(__FILE__, array( 'WdmWSAIInstall', 'createTables' ));

add_action('woocommerce_admin_order_data_after_order_details', 'wdm_woocommerce_admin_order_data_after_order_details', 10, 1);

/**
 * Display text field for IP mapping
 * @param  [type] $subscription [description]
 * @return [type]               [description]
 */
function wdm_woocommerce_admin_order_data_after_order_details($subscription)
{
    if (is_a($subscription, 'WC_Subscription')) {
        if ($subscription->post->post_status != 'auto-draft') {
            global $wpdb;
            $wdm_wsai_ip_mapping    = $wpdb->prefix . 'wsai_ip_mapping';
            $ip_address = '';
            $order_id=get_associated_order($subscription->post);
            if (!empty($order_id)) {
                $wsai_ip_mapping = $wpdb->get_results("SELECT ip_address FROM {$wdm_wsai_ip_mapping} WHERE post_id = ".$order_id);
            }
            if (isset($wsai_ip_mapping) && ! empty($wsai_ip_mapping)) {
                $ip_address = $wsai_ip_mapping[0]->ip_address;
            } else {
                $ip_address = get_post_meta($order_id, 'wdm_subscription_ip_address', true);
            }
            echo '<lable for="wdm_ip">Enter IP Address</lable><input type="text" id="wdm_ip" name="wdm_ip" value="'. $ip_address .'" />';
        } else {
            echo '<lable for="wdm_ip">Enter IP Address</lable><input type="text" id="wdm_ip" name="wdm_ip" disabled/>';
            echo '<span class="woocommerce-help-tip" id="wdm_subscription_tip"></span>';
        }
    }

}

add_action('woocommerce_process_shop_order_meta', 'wdm_woocommerce_order_edit_product', 10, 2);

/**
 * Save IP address on save subscription
 * @param  [type] $order_id [description]
 * @param  [type] $post     [description]
 * @return [type]           [description]
 */
function wdm_woocommerce_order_edit_product($order_id, $post)
{
    global $wpdb;
    $wdm_wsai_ip_mapping    = $wpdb->prefix . 'wsai_ip_mapping';

    $post_parent=get_associated_order($post);
    if (!filter_var($_POST['wdm_ip'], FILTER_VALIDATE_IP) === false) {
        if ($post->post_status == 'wc-active') {
            if (!empty($post_parent)) {
                $wsai_ip_mapping = $wpdb->get_results("SELECT id FROM {$wdm_wsai_ip_mapping} WHERE post_id = "  .$post_parent);
                $mapping_count          = count($wsai_ip_mapping);
                if ($mapping_count == 0) {
                    $insertrec = $wpdb->insert($wdm_wsai_ip_mapping, array(
                        'post_id'           => $post_parent,//$post->post_parent,//$post->post_parent,// $order_id,
                        'ip_address'        => $_POST['wdm_ip'],
                        ), array(
                        '%d',
                        '%s',
                        ));
                } else {
                    $updated = $wpdb->update($wdm_wsai_ip_mapping, array(
                        'ip_address'          => $_POST['wdm_ip'],
                        ), array( 'post_id' => $post_parent));
                }
            }
        } elseif ($post->post_status == 'wc-on-hold') {
            update_post_meta($post_parent, 'wdm_subscription_ip_address', $_POST['wdm_ip']);
        }
    }
}



add_action('woocommerce_subscription_status_updated', 'wdm_woocommerce_subscription_status_updated', 10, 3);
/**
 * Store active subscription in custom table and other status IP's in order meta
 * @param  Object $subscription [description]
 * @param  string $new_status   [description]
 * @param  string $old_status   [description]
 * @return [type]               [description]
 */
function wdm_woocommerce_subscription_status_updated($subscription, $new_status = '', $old_status = '')
{
    global $wpdb;
    $wdm_wsai_ip_mapping    = $wpdb->prefix . 'wsai_ip_mapping';
    $order_id=get_associated_order($subscription->post);
    if ($new_status == 'active') {
        $wdm_ip_addr = get_post_meta($order_id, 'wdm_subscription_ip_address', true);
        $wpdb->insert($wdm_wsai_ip_mapping, array(
                    'post_id'           => $order_id,// $order_id,
                    'ip_address'          => $wdm_ip_addr,
                    ), array(
                    '%d',
                    '%s',
                    ));
    } else {
        if (!empty($order_id)) {
            $wsai_ip_mapping = $wpdb->get_results("SELECT ip_address FROM {$wdm_wsai_ip_mapping} WHERE post_id = ".$order_id);
        }
        if (isset($wsai_ip_mapping) && ! empty($wsai_ip_mapping)) {
            $ip_address = $wsai_ip_mapping[0]->ip_address;
            update_post_meta($order_id, 'wdm_subscription_ip_address', $ip_address);
            $wpdb->delete($wdm_wsai_ip_mapping, array( 'post_id' => $order_id ));
        }
    }
}

add_action('admin_menu', 'wporg_custom_admin_menu');

/**
 * Add Custom menu page
 * @return [type] [description]
 */
function wporg_custom_admin_menu()
{

    add_options_page(
        'IP Settings',
        'IP Settings',
        'manage_options',
        'ip-settings-page',
        'ip_settings_page'
    );
}
wp_enqueue_script('wdm_enqueue_template_script', plugins_url('/js/wdm_woo_subscription_addon_for_ips.js', __FILE__));
wp_localize_script('wdm_enqueue_template_script', 'ajax_url', admin_url('admin-ajax.php'));

/**
 * Custom menu page callback function
 * @return [type] [description]
 */
function ip_settings_page()
{

    //wp_enqueue_script('wdm_enqueue_template_script', plugins_url('/js/wdm_woo_subscription_addon_for_ips.js', __FILE__));
    wp_enqueue_style('wdm_enqueue_template_style', plugins_url('/css/wdm_woo_subscription_addon_for_ips.css', __FILE__));
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
    $page_template_instance = PageTemplater::get_instance();
    $whitelisted_ips_data = get_option('whitelisted_ips');
    $social_traffic_data = get_option('social_traffic');
    $wdm_social_domain_list = get_option('wdm_social_domain_list');

    echo '<h2>Subscription Settings</h2>
	<div id="wdm_message" class="updated fade"></div>
 	<form id="whitelisted_ip"  action="save_ip_templates()">

    <table>
    <tr><td><label for="whiltelisted_ips"> Select Template for whitelisted ip-addresses </label></td>
    	<td><select class="whiltelisted_ips" name ="whiltelisted_ips" id="whiltelisted_ips">';
    foreach ($page_template_instance->templates as $value) {
        if ($whitelisted_ips_data == $value) {
            echo '<option selected>'.$value.'</option>';
        } else {
            echo '<option>'.$value.'</option>';
        }
    }
    echo '</select></td>
	</tr><tr>
	<td><label for="whiltelisted_ips"> Select Template for social traffic </label></td>
    <td><select class="social_traffic" name ="social_traffic" id="social_traffic">';
    foreach ($page_template_instance->templates as $value) {
        if ($social_traffic_data == $value) {
            echo '<option selected>'.$value.'</option>';
        } else {
            echo '<option>'.$value.'</option>';
        }
    }
    echo '</select></td></tr>';
    echo '<tr height="50"><th colspan="2"><input type="submit" value = "Save Templates" class="wdm_save_templates button button-primary"></th></tr>
        <tr><td colspan=2><h2>Add Social Domain</h2></td></tr>
        <tr><td>Enter Social Domain Name  </td>
        <td><input type=text id="wdm_social_domain"></td></tr>
        <tr height="50"><th colspan="2"><input type="submit" value = "Add Social Domain" class="wdm_add_social_domains button button-primary"></th></tr>
    </table>
    <h2>Social Domain List</h2>';
    echo '<table id="wdm_social_domain_table"><tr><th>Domain Name</th><th>Action</th></tr>';
    foreach ($wdm_social_domain_list as $social_domain) {
        echo '<tr id='.$social_domain.'><td class="social_domain">'.$social_domain.'</td><td><i class="wdm_remove_social_domain fa fa-times"></i></td></tr>';
    }
    echo '</table>';
    echo '</form>';
}


add_action('plugins_loaded', array( 'PageTemplater', 'get_instance' ));

/**
 * Save custom templates for redirect
 * @return [type] [description]
 */
function save_ip_templates()
{

    if (isset($_POST['whitelisted_ips']) && ! empty($_POST['whitelisted_ips'])) {
        update_option('whitelisted_ips', $_POST['whitelisted_ips']);
    }
    if (isset($_POST['social_traffic']) && ! empty($_POST['social_traffic'])) {
          update_option('social_traffic', $_POST['social_traffic']);
    }
}

add_action('wp_ajax_save_ip_templates', 'save_ip_templates');
add_action('wp_ajax_nopriv_save_ip_templates', 'save_ip_templates');


add_action('wp_ajax_save_social_domain', 'save_social_domain');
add_action('wp_ajax_nopriv_save_social_domain', 'save_social_domain');

/**
 * Save Social domains
 * @return [type] [description]
 */
function save_social_domain()
{

    if (isset($_POST['wdm_social_domain']) && ! empty($_POST['wdm_social_domain'])) {
        $social_domain = $_POST['wdm_social_domain'];
        $wdm_social_domain_list = get_option('wdm_social_domain_list');
        if (empty($wdm_social_domain_list)) {
            $wdm_social_domain_list = array( $social_domain );
            echo '<tr id='.$social_domain.'><td class="social_domain">'.$social_domain.'</td><td><i class="wdm_remove_social_domain fa fa-times"></i></td></tr>';
        } else {
            if (! in_array($social_domain, $wdm_social_domain_list)) {
                array_push($wdm_social_domain_list, $social_domain);
                echo '<tr id='.$social_domain.'><td class="social_domain">'.$social_domain.'</td><td><i class="wdm_remove_social_domain fa fa-times"></i></td></tr>';
            } else {
                echo 'false';
            }
        }
        update_option('wdm_social_domain_list', $wdm_social_domain_list);
    }

    die();
}

add_action('wp_ajax_save_ip_templates', 'save_ip_templates');
add_action('wp_ajax_nopriv_save_ip_templates', 'save_ip_templates');


add_action('wp_ajax_save_social_domain', 'save_social_domain');
add_action('wp_ajax_nopriv_save_social_domain', 'save_social_domain');


add_action('wp_ajax_delete_social_domain', 'delete_social_domain');
add_action('wp_ajax_nopriv_delete_social_domain', 'delete_social_domain');

/**
 * delete social domains from list
 * @return [type] [description]
 */
function delete_social_domain()
{

    if (isset($_POST['wdm_social_domain']) && ! empty($_POST['wdm_social_domain'])) {
        $wdm_social_domain_list = get_option('wdm_social_domain_list');
        if (! empty($wdm_social_domain_list)) {
            if (($key = array_search($_POST['wdm_social_domain'], $wdm_social_domain_list)) !== false) {
                unset($wdm_social_domain_list[$key]);
            }
        }
        update_option('wdm_social_domain_list', $wdm_social_domain_list);
        echo $_POST['wdm_social_domain'];
    }
}


/**
 * Get associated order with subscription
 * @param  [type] $post Subscription
 * @return integer      order_id
 */
function get_associated_order($post)
{
    $post_parent='';
    if (isset($post->post_parent) && $post->post_parent != 0) {
        $post_parent=$post->post_parent;
    } else {
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'meta_query' => array(
            array(
            'key' => '_subscription_renewal',
            'value' => $post->ID,
            )
            )
            );
            $postslist = get_posts($args);
        if (isset($postslist[0]) && ! empty($postslist[0])) {
            $post_parent=$postslist[0]->ID;//get_post_meta($subscription->id, '_subscription_renewal', true);
        }
    }
    return $post_parent;
}
