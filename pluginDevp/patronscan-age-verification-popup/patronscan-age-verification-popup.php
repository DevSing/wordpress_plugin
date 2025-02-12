<?php
/*
Plugin Name: Patronscan Age Verification Popup
Description: This plugin shows an age verification popup using Patronscan and blocks access until the user is verified.
Version: 1.4
Author: DevP
Author URI: https://thethinktech.com/
*/

// Enqueue scripts and styles
function patronscan_age_verification_enqueue_scripts() {
    wp_enqueue_style('patronscan-popup-css', plugins_url('patronscan-popup.css', __FILE__));
    wp_enqueue_script('patronscan-popup-js', plugins_url('patronscan-popup.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'patronscan_age_verification_enqueue_scripts');

// Create popup HTML
function patronscan_age_verification_popup() {
    if (is_user_logged_in()) {
        // If the user is logged-in, no need for the popup
        return;
    }
    ?>
    <div id="patronscan-age-verification-popup" class="patronscan-popup" style="display: none;">
        <div class="patronscan-popup-content">
            <h2>Adults Only</h2>
            <p class="fw-bold">You must be of legal age to visit this website, verify your age with Patronscan to continue.</p>
            <p>You will only have to do this once per device or account.</p> <br>
            <a href="https://verify.patronscan.com?token=C28153D4-10ED-4E7C-AA73-6137D372CA32&redirectUrl=<?php echo home_url(); ?>"
               class="btn-verify patronscan-button">Verify Your Age</a> <br> <br><br>
            <a href="/my-account/" class="btn-login patronscan-button">Login with an Existing Account</a>
            <br><br>
            <a href="https://google.ca" class="patronscan-button text-red">I'm not of legal smoking age</a>
            <p>It is illegal to sell or resell to minors. For additional information contact us at</p>
            <a href="mailto: support@altvape.ca">support@altvape.ca</a>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'patronscan_age_verification_popup');

// Ajax to handle API call
function patronscan_verify_age_callback() {
    if (isset($_GET['id'])) {
        $query_id = sanitize_text_field($_GET['id']);
        $api_url = "https://core.prod.patronscan.servallapps.com/api/v1/scanning/verify-auth/{$query_id}";

        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'apiKey' => 'C28153D4-10ED-4E7C-AA73-6137D372CA32'
            )
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'API request failed'));
        }

        $body = wp_remote_retrieve_body($response);
        $api_res = json_decode($body, true);

        if ($api_res['success'] && isset($api_res['data'])) {
            if (!$api_res['data']['isFake']) {
                setcookie('ps_vrf_id', sanitize_text_field($_GET['id']), time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN); // Set cookie for 30 days
                wp_send_json_success(array('message' => 'ID is valid', 'data' => $api_res['data']));
            } else {
                // Do not clear the cookie, just return error to trigger the popup again
                wp_send_json_error(array('message' => 'Your ID is not valid, please enter a valid ID.'));
            }
        } else {
            wp_send_json_error(array('message' => 'Invalid response from API.'));
        }
    } else {
        wp_send_json_error(array('message' => 'No ID provided in the URL.'));
    }

    wp_die();
}
add_action('wp_ajax_nopriv_patronscan_verify_age', 'patronscan_verify_age_callback');
add_action('wp_ajax_patronscan_verify_age', 'patronscan_verify_age_callback');

// JS Script to handle popup, cookie, API request, and redirection
add_action('wp_footer', function() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function getCookie(name) {
                let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                if (match) return match[2];
            }

            const queryId = new URLSearchParams(window.location.search).get('id');
            const ps_vrf_id = getCookie('ps_vrf_id');

            // Check if user is on /my-account page
            if (window.location.pathname === '/my-account/') {
                $('#patronscan-age-verification-popup').hide(); // Close popup on login page
                return;
            }

            // If cookie is present, do a background check but don't show popup or alerts
            if (ps_vrf_id) {
                // User is already verified, no need to show the popup or alerts
                return;
            }

            // No cookie, show the popup
            $('#patronscan-age-verification-popup').show();

            // Check if we have a query ID for first-time verification
            if (queryId) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'GET',
                    data: {
                        action: 'patronscan_verify_age',
                        id: queryId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close the popup if ID is valid, save cookie, and redirect to siteurl
                            $('#patronscan-age-verification-popup').hide();
                            document.cookie = "ps_vrf_id=" + queryId + "; max-age=" + (30 * 24 * 60 * 60) + "; path=/";
                            window.location.href = "<?php echo home_url(); ?>"; // Redirect to siteurl
                        } else {
                            // Show alert and keep the popup open if ID is invalid
                            alert(response.data.message);
                            $('#patronscan-age-verification-popup').show();
                        }
                    },
                    error: function() {
                        alert('Error while verifying age.');
                    }
                });
            }

            // Redirect to google.ca if user clicks on "I'm not of legal smoking age"
            $('a.text-red').on('click', function(e) {
                e.preventDefault();
                window.location.href = "https://google.ca";
            });

            // Redirect to my-account page on login link
            $('a.btn-login').on('click', function(e) {
                $('#patronscan-age-verification-popup').hide(); // Close popup on login click
            });
        });
    </script>
    <?php
});