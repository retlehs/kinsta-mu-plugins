<?php namespace Kinsta ?>
<div class="wrap">

    <div class='kinsta-page-bar'>
        <img class='logo' src='<?php echo KinstaTools::shared_resource_url('shared') ?>/images/logo-dark.svg' height='16px'>
        <h3><?php _e( 'Kinsta MU Testing', 'kinsta-cache' ) ?></h3>
    </div>

    <div class='kinsta-page-wrapper'>

        <div class='kinsta-main-content'>

            <div class='kinsta-box'>

                <div class='kinsta-box-title-bar'>
                    <h3><?php _e( 'Create Test Content', 'kinsta-cache' ) ?></h3>
                </div>

                <div class='kinsta-box-content'>
                    <div class='content mb22'>

                        <?php if(!empty( $_GET['kinsta_success'] ) && $_GET['kinsta_success'] == 'added_content' ) : ?>
                            <div class="kinsta-message kinsta-message-success">
                                Test content has been added successfully. To remove all test content click the Remove Test Content button.
                            </div>
                        <?php endif ?>

                        <?php if(!empty( $_GET['kinsta_success'] ) && $_GET['kinsta_success'] == 'removed_content' ) : ?>
                            <div class="kinsta-message kinsta-message-success">
                                Test content has been removed successfully.
                            </div>
                        <?php endif ?>

                        <p><?php _e( 'To create test content press the button below. It will create 50 posts, 5 pages and 50 books with a number of categories, tags and genres.', 'kinsta-cache') ?>
                        </p>

                        <?php
                            $url = wp_nonce_url( admin_url('admin-ajax.php?action=kinsta_add_mu_test_content'), 'kinsta_add_mu_test_content', 'kinstanonce' );
                        ?>

                        <a href="<?php echo $url ?>" class='button button-primary'>Add Test Content</a>

                        <?php
                            $has_test_content = get_option( 'kinsta_has_mu_test_content' );
                            $disabled = ( empty( $has_test_content) ) ? 'disabled="disabled"' : '';
                            $url = wp_nonce_url( admin_url('admin-ajax.php?action=kinsta_remove_mu_test_content'), 'kinsta_remove_mu_test_content', 'kinstanonce' );
                        ?>
                        <a href="<?php echo $url ?>" <?php echo $disabled ?> class='button'>Remove Test Content</a>
                    </div>

                </div>
            </div>




            <div class='kinsta-box' style="margin-top:22px">

                <div class='kinsta-box-title-bar'>
                    <h3><?php _e( 'Test Caching Mechanism', 'kinsta-cache' ) ?></h3>
                </div>

                <div class='kinsta-box-content'>
                    <div class='content mb22'>
                        <?php include( 'partials/test-caching-mechanism.php' ) ?>
                    </div>

                </div>
            </div>

        </div>

        <div class='kinsta-sidebar'>

            <div class='kinsta-box kinsta-widget'>
                <div class='kinsta-box-title-bar' style="background:#E0202F">
                    <h3><?php _e( 'Important Information', 'kinsta-mu-plugins' ) ?></h3>
                </div>
                <div class="kinsta-box-content kinsta-flex">
                    <div><?php _e( 'This module is for Kinsta developer testing only. If you see this page please get in touch with us through your <a href="https://my.kinsta.com/">Kinsta Dashboard</a>.', 'kinsta-cache' ); ?>.</div>
                </div>
            </div>
        </div>

    </div>

</div>
