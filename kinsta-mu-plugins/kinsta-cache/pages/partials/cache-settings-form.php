<?php namespace Kinsta ?>
<form method="post">

    <div class='kinsta-box'>

        <fieldset class='mb22'>
            <legend class='kinsta-box-title-bar kinsta-box-title-bar__small mb22'><h3><?php _e( 'Custom URLs To Purge', 'kinsta-cache' ) ?></h3></legend>

            <p>
                <?php echo sprintf( __('You can add custom paths to purge whenever your site is updated. Please see our <a href="%s">documentation</a> for more information on how to use this feature effectively.', 'kinsta-cache' ), KINSTA_CACHE_DOCS_URL ) ?>
            </p>

            <div id="custom-url-form">
                <h3>Add A Custom URL</h3>
                <div id="custom-url-form-fields">
                    <?php KinstaTools::kinsta_select_field( 'custom-url-type', 'custom-url-type', 'single', '', false, false, array(
                        'single' => 'Single Path',
                        'group' => 'Group Path'
                    ) ) ?>

                    <span onClick="jQuery('#addURLField').focus()" class="prefix"><?php echo home_url() ?>/</span><input  id="addURLField" type="text" placeholder="Enter a Path" />
                    <input id="addURLSubmit" type="submit" value="Add URL">
                </div>
            </div>

            <?php


                $additional_paths = get_option( 'kinsta-cache-additional-paths' );
                $display = ( empty($additional_paths) ) ? 'none' : 'table';
                echo '<table id="additionalURLTable" class="kinsta-table" style="margin-top:22px; display:'.$display.'">';
                echo '<thead><tr><th>Type</th><th>Path</th><th></th></tr></thead>';
                echo '<tbody>';

                if( !empty( $additional_paths ) ) {
                    foreach( $additional_paths as $path ) {
                        echo '<tr>';
                        echo '<td>' . $path['type'] . '</td>';
                        echo '<td>/' . $path['path'] . '</td>';
                        echo '<td><a class="removePath" href="#">remove</a></td>';
                        echo '</tr>';
                    }
                }
                echo '</tbody>';
                echo '</table>';

            ?>




        </fieldset>


    </div>

    <?php wp_nonce_field( 'save_plugin_options', 'kinsta_nonce' ); ?>
    <input type="hidden" name="action" value='save_plugin_options'>


    <script type="text/javascript">
        jQuery(document).on('click', '#addURLSubmit', function() {
            var path = jQuery('#addURLField').val();
            var type = jQuery('select[name="custom-url-type"]').val()
            if( path === '' || path === null || typeof path === 'undefined' ) {
                return false
            }
            jQuery.ajax({
                url: ajaxurl,
                method: 'post',
                data: {
                    action: 'kinsta_save_custom_path',
                    kinsta_nonce: jQuery('#kinsta_nonce').val(),
                    path: path,
                    type: type
                },
                success: function( result ) {
                    jQuery('#addURLField').val('')
                    if( jQuery('#additionalURLTable tbody tr').length === 0 ) {
                        jQuery('#additionalURLTable').show();
                    }

                    var row = jQuery('<tr></tr>');
                    row.append('<td>'+type+'</td>')
                    row.append('<td>/'+path+'</td>')
                    row.append('<td><a class="removePath" href="#">remove</a></td>')
                    jQuery('#additionalURLTable').append(row)
                }
            })
            return false;
        })

        jQuery(document).on('click', '.removePath', function() {
            var row = jQuery(this).parents('tr:first')
            var index = row.index()
            jQuery.ajax({
                url: ajaxurl,
                method: 'post',
                data: {
                    action: 'kinsta_remove_custom_path',
                    kinsta_nonce: jQuery('#kinsta_nonce').val(),
                    index: index,
                },
                success: function( result ) {
                    row.fadeOut( function() {
                        row.remove();
                        if( jQuery('#additionalURLTable tbody tr').length === 0 ) {
                            jQuery('#additionalURLTable').hide();
                        }

                    })

                }
            })
        })
    </script>

</form>
