<?php
namespace Kinsta;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>
<div class="wrap">

    <div class='kinsta-page-bar'>
        <?php if( KINSTAMU_WHITELABEL === false ) : ?>
            <img class='logo' src='<?php echo KinstaTools::shared_resource_url('shared') ?>/images/logo-dark.svg' height='16px'>
        <?php elseif( defined('KINSTAMU_LOGO') ) : ?>
            <img class='logo' src='<?php echo KINSTAMU_LOGO ?>' height='32px'>
        <?php endif ?>
        <h3><?php _e( 'Cache Control', 'kinsta-cache' ) ?></h3>
    </div>

    <div class='kinsta-page-wrapper'>

        <div class='kinsta-main-content'>

            <div class='kinsta-box'>

                <div class='kinsta-box-title-bar'>
                    <h3><?php _e( 'Kinsta Cache', 'kinsta-cache' ) ?></h3>
                </div>

                <div class='kinsta-box-content'>
                    <div class='content mb22'>
                        <p><?php _e( 'Your site uses our full page and object caching technology to remain lightning fast. We purge single pages and key pages such as the home page immediately and impose a minimal throttle time on archive pages. This ensures high availablity at all times.', 'kinsta-cache') ?>
                        </p>
                    </div>

                    <?php include( 'partials/cache-settings-form.php') ?>

                </div>
            </div>

        </div>

        <div class='kinsta-sidebar'>

            <?php if( $this->KinstaCache->has_object_cache ) : ?>
                <?php include( 'partials/sidebar-purge-has-object-cache.php' ) ?>
            <?php else : ?>
                <?php include( 'partials/sidebar-purge-has-object-cache.php' ) ?>
            <?php endif ?>

            <?php
                if( KINSTAMU_WHITELABEL === false ) {
                    include( 'partials/sidebar-support.php' );
                }
            ?>
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
