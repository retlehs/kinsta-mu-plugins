<?php namespace Kinsta ?>
<div class="wrap">

    <div class='kinsta-page-bar'>
        <img src='<?php echo plugin_dir_url( __FILE__ ) ?>../../shared/images/logo-dark.svg' height='16px'>
        <h3><?php _e( 'Cache Control', 'kinsta-cache' ) ?></h3>
    </div>

    <div class='kinsta-page-wrapper'>

        <div class='kinsta-main-content'>

            <div class='kinsta-box'>

                <div class='kinsta-box-title-bar'>
                    <h3><?php _e( 'Cache Settings', 'kinsta-cache' ) ?></h3>
                </div>

                <div class='kinsta-box-content'>
                    <div class='content mb22'>
                        <p><?php _e( 'Your site is uses our full page and object caching technology to remain lightning fast. We recommend leaving all options on their default settings for optimal performance.', 'kinsta-cache') ?>
                        </p>
                    </div>

                    <?php include( 'partials/cache-settings-form.php') ?>

                </div>
            </div>

        </div>

        <div class='kinsta-sidebar'>

            <?php if( $this->KinstaCache->KinstaCachePurge->has_object_cache ) : ?>
                <?php include( 'partials/sidebar-purge-has-object-cache.php' ) ?>
            <?php else : ?>
                <?php include( 'partials/sidebar-purge-no-object-cache.php' ) ?>
            <?php endif ?>

            <?php include( 'partials/sidebar-support.php' ) ?>
        </div>

    </div>

</div>


<script>
jQuery(document).on('click', '.kinsta-clear-cache', function() {
    var element = jQuery(this);
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php') ?>',
        type: 'post',
        data: {
            kinsta_nonce: element.attr('data-nonce'),
            action: element.attr( 'data-action' )
        }
    })
    return false;
})
</script>
