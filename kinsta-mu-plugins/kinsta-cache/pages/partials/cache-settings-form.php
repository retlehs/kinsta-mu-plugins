<?php namespace Kinsta ?>
<form method="post">

    <div class='kinsta-box'>

        <fieldset class='mb22'>
            <legend class='kinsta-box-title-bar kinsta-box-title-bar__small mb22'><h3><?php _e( 'General Options', 'kinsta-cache' ) ?></h3></legend>

            <?php
                KinstaTools::kinsta_number_field(
                    'options[page_depth_blog]',
                    $this->KinstaCache->settings['options']['page_depth_blog'],
                    __( 'Clear blog page depth', 'kinsta-cache')
                );
                KinstaTools::kinsta_number_field(
                    'options[page_depth_archives]',
                    $this->KinstaCache->settings['options']['page_depth_archives'],
                    __( 'Clear archive page depth', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'options[purge_blog_feeds]',
                    $this->KinstaCache->settings['options']['purge_blog_feeds'],
                    __( 'Clear blog feeds', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'options[purge_static_home]',
                    $this->KinstaCache->settings['options']['purge_static_home'],
                    __( 'Clear static home page', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'options[purge_archive_feeds]',
                    $this->KinstaCache->settings['options']['purge_archive_feeds'],
                    __( 'Clear archive feeds', 'kinsta-cache')
                );

                KinstaTools::kinsta_switch(
                    'options[purge_date_archives]',
                    $this->KinstaCache->settings['options']['purge_date_archives'],
                    __( 'Clear date archives', 'kinsta-cache')
                );

                KinstaTools::kinsta_switch(
                    'options[has_mobile_plugin]',
                    $this->KinstaCache->settings['options']['has_mobile_plugin'],
                    __( 'Do you use a dedicated plugin to make your site\'s mobile version?', 'kinsta-cache')
                );
            ?>

        </fieldset>


        <fieldset class='mb22'>
            <legend class='kinsta-box-title-bar kinsta-box-title-bar__small mb22'><h3><?php _e( 'Clear Blog Cache', 'kinsta-cache' ) ?></h3></legend>

            <?php
                KinstaTools::kinsta_switch(
                    'rules[blog][post_added]',
                    $this->KinstaCache->settings['rules']['blog']['post_added'],
                    __( 'when a post (or page/custom post) is <strong>published</strong>', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'rules[blog][post_modified]',
                    $this->KinstaCache->settings['rules']['blog']['post_modified'],
                    __( 'when a <strong>published post</strong> (or page/custom post) is <strong>updated</strong>', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'rules[blog][post_unpublished]',
                    $this->KinstaCache->settings['rules']['blog']['post_unpublished'],
                    __( 'when a post is <strong>unpublished</strong> (trashed, drafted, etc.)', 'kinsta-cache')
                );
            ?>

        </fieldset>

        <fieldset class='mb22'>
            <legend class='kinsta-box-title-bar kinsta-box-title-bar__small mb22'><h3><?php _e( 'Clear Singular Page Cache', 'kinsta-cache' ) ?></h3></legend>

            <?php
                KinstaTools::kinsta_switch(
                    'rules[post][post_added]',
                    $this->KinstaCache->settings['rules']['post']['post_added'],
                    __( 'when a post (or page/custom post) is <strong>published</strong>', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'rules[post][post_modified]',
                    $this->KinstaCache->settings['rules']['post']['post_modified'],
                    __( 'when a <strong>published post</strong> (or page/custom post) is <strong>updated</strong>', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'rules[post][post_unpublished]',
                    $this->KinstaCache->settings['rules']['post']['post_unpublished'],
                    __( 'when a post is <strong>unpublished</strong> (trashed, drafted, etc.)', 'kinsta-cache' )
                );
                KinstaTools::kinsta_switch(
                    'rules[post][comment_added]',
                    $this->KinstaCache->settings['rules']['post']['comment_added'],
                    __( 'when a comment is <strong>published</strong>', 'kinsta-cache' )
                );
                KinstaTools::kinsta_switch(
                    'rules[post][comment_modified]',
                    $this->KinstaCache->settings['rules']['post']['comment_modified'],
                    __( 'when a comment is <strong>updated</strong>', 'kinsta-cache' )
                );
                KinstaTools::kinsta_switch(
                    'rules[post][comment_unpublished]',
                    $this->KinstaCache->settings['rules']['post']['comment_unpublished'],
                    __( 'when a comment is <strong>unpublished</strong> (marked as spam, deleted, etc.)', 'kinsta-cache' )
                );

            ?>

        </fieldset>

        <fieldset class='mb22'>
            <legend class='kinsta-box-title-bar kinsta-box-title-bar__small mb22'><h3><?php _e( 'Clear Archive Caches', 'kinsta-cache' ) ?></h3></legend>

            <?php
                KinstaTools::kinsta_switch(
                    'rules[archive][post_added]',
                    $this->KinstaCache->settings['rules']['archive']['post_added'],
                    __( 'when a post (or page/custom post) is <strong>published</strong>', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'rules[archive][post_modified]',
                    $this->KinstaCache->settings['rules']['archive']['post_modified'],
                    __( 'when a <strong>published post</strong> (or page/custom post) is <strong>updated</strong>', 'kinsta-cache')
                );
                KinstaTools::kinsta_switch(
                    'rules[archive][post_unpublished]',
                    $this->KinstaCache->settings['rules']['archive']['post_unpublished'],
                    __( 'when a post is <strong>unpublished</strong> (trashed, drafted, etc.)', 'kinsta-cache')
                );
            ?>
        </fieldset>

    </div>

    <?php wp_nonce_field( 'save_plugin_options', 'kinsta_nonce' ); ?>
    <input type="hidden" name="action" value='save_plugin_options'>
    <button type="submit" class='kinsta-button kinsta-loader' value='<?php _e( 'Save Changes', 'kinsta-cache' ) ?>' data-progressText='<?php _e( 'Saving...', 'kinsta-cache' ) ?>' data-completedText='<?php _e( 'Saved', 'kinsta-cache' ) ?>' data-type='reload'><?php _e( 'Save Changes', 'kinsta-cache' ) ?></button>

</form>
