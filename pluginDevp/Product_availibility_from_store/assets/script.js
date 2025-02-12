jQuery(document).ready(function($) {
    function updateAvailabilityStatus() {
        var product_id = $('#product_id').val();
        var variation_id = $('form.cart input[name="variation_id"]').val();

        if (variation_id) {
			
			// Show loading spinner
            $('#availability-status').html('<p class="inventory-status-checking">Checking Inventory Status...</p>');
			
            $.ajax({
                url: product_availibility_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'product_availibility_from_store_check_availability',
                    product_id: product_id,
                    variation_id: variation_id
                },
                success: function(response) {
                    if (response.success) {
                        $('#availability-status').html(response.data);
                    } else {
                     //   $('#availability-status').html('<p>Error: ' + response.data + '</p>');
                        $('#availability-status').html('<p class="inventory-initiak-status">Please select a variant to know availability status.</p>');
                    }
                }
            });
        } else {
            $('#availability-status').html('<p class="inventory-initiak-status">Please select a variant to know availability status.</p>');
        }
    }

    // Trigger the function on variant selection
    $(document.body).on('found_variation', updateAvailabilityStatus);
    $(document.body).on('reset_data', updateAvailabilityStatus);
});