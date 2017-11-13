<?php
$has_test_content = get_option( 'kinsta_has_mu_test_content' );
if( !$has_test_content ) :
?>
    <p><?php _e( 'For security reasons you can only test the cache mechanism with test data. Please create test data using the Add Test Content button above.', 'kinsta-cache') ?>
    </p>
<?php else : ?>

    <p><?php _e( 'Here are 6 random posts from the site: 2 posts, 2 books and 2 pages. Click on the update button to see which paths would be cleared on a post update or the comment button to see what happens when a comment is added.', 'kinsta-cache') ?>
    </p>

    <?php
    $post_ids = array();
    foreach( array('post', 'book', 'page') as $type ) {
        $args = array(
           'post_type' => $type,
           'meta_query' => array(
               array(
                   'key' => 'kinsta_mu_test_data',
                   'value' => '',
                   'compare' => '!=',
               )
           ),
           'fields' => 'ids',
           'posts_per_page' => 2
        );
        $posts = new \WP_Query( $args );

        $post_ids = array_merge( $post_ids, $posts->posts );
    }

    $cache_posts = new \WP_Query( array(
        'post_type' => 'any',
        'post__in' => $post_ids,
        'ignore_sticky_posts' => 1,
        'orderby' => 'post__in'
    ));

    if( $cache_posts->have_posts() ) {
        echo '<div class="cache-posts">';
        while( $cache_posts->have_posts() ) {
            $cache_posts->the_post();
            $term_list = array();
            $terms = wp_get_object_terms( get_the_ID(), array('category', 'post_tag', 'genre'));
            foreach( $terms as $term ) {
                $term_list[$term->taxonomy] = ( empty($term_list[$term->taxonomy]) ) ? $term->name : $term_list[$term->taxonomy] . ', ' . $term->name;
            }

            $taxonomies = array(
                'category' => 'Categories',
                'post_tag' => 'Tags',
                'genre'    => 'Genres'
            );

            ?>
            <div class="cache-post" style="border-bottom:1px solid #eee; padding:22px 0">
                <div style="display:flex; align-items:center;">
                    <div style="width:50px; padding:6px 11px; background: #f1f1f1; color: #666; text-align: center; margin-right:11px"><?php echo get_post_type() ?></div>
                    <h3><?php the_title() ?></h3>
                    <div style="margin-left: auto">
                        <a href='#' data-type="post" data-post_id="<?php the_ID() ?>" class='button button-primary cache-test-cache'>Update</a>
                        <a href='#' data-type="comment" data-post_id="<?php the_ID() ?>" class='button button-primary cache-test-cache'>Comment</a>
                    </div>
                </div>
                <?php
                if( !empty( $term_list ) ) {
                    echo '<div style="margin-left:61px">';
                    foreach( $taxonomies as $slug => $name ) {
                        if ( !empty( $term_list[$slug] ) ) {
                            echo '<div>';
                            echo $name . ': ' . $term_list[$slug];
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                }
                ?>


            </div>
            <?php
        }
        echo '</div>';
    }
?>

<script type="text/javascript">

    function displayToDecimals( number, decimals ) {
        var s = number.toString();
        var d = s.indexOf('.')
        if( d === -1 ) {
            return s;
        }

        return s.substr( 0, d + decimals + 1 );

    }
    jQuery(document).on('click', '.cache-test-cache', function() {

        var post_id = jQuery(this).attr('data-post_id');
        var type = jQuery(this).attr('data-type');
        var parent = jQuery(this).parents('.cache-post:first');

        parent.find('.kinsta-cache-result').remove();
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'kinsta_test_cache_post',
                type: type,
                post_id: post_id,
                kinstanonce: "<?php echo wp_create_nonce( 'test_cache_post' ) ?>"
            },
            success: function( response ) {
                var immediateStatusStyle = (response.immediate.status >= 400 ) ? 'background:#FB8686' : 'background: #78DB7B';
                var throttledStatusStyle = (response.throttled.status >= 400 ) ? 'background:#FB8686' : 'background: #78DB7B';

                var immediatePaths = '';
                for (var key in response.requests.immediate ) {
                    var value = response.requests.immediate[key];
                    immediatePaths = immediatePaths + '<tr><td style="padding-left:0px; padding-right:22px; padding-top:4px; padding-bottom:4px; color: #222">' + value + '</td><td style="color: #999">' + key + '</td></tr>'
                }

                var throttledPaths = '';
                for (var key in response.requests.throttled ) {
                    var value = response.requests.throttled[key];
                    throttledPaths = throttledPaths + '<tr><td style="padding-left:0px; padding-right:22px; padding-top:4px; padding-bottom:4px; color: #222">' + value + '</td><td style="color: #999">' + key + '</td></tr>'
                }

                var timeToCompile = response.time.sendrequest - response.time.start;
                var timeToResponse = response.time.end - response.time.sendrequest
                var timeTotal = response.time.end - response.time.start

                var display = jQuery(`
                <div class="kinsta-cache-result" style="margin-left:61px; padding:22px; background:#f1f1f1; margin-top:22px">
                    <h3 style="margin:0 0 11px 0;">Responses</h3>
                    <div style='margin-bottom:3px'><span style="display:inline-block; margin-right: 6px; padding:3px 11px; color:#fff; ` + immediateStatusStyle + `">` + response.immediate.status + `</span> Immediate Request</div>

                    <div><span style="display:inline-block; margin-right:6px; padding:3px 11px; color:#fff; ` + throttledStatusStyle + `">` + response.throttled.status + `</span> Throttled Request</div>


                    <h3 style="margin:22px 0 11px 0;">Timing</h3>
                    <table>
                    <tr>
                        <td style="padding-left:0px; padding-right:22px; padding-top:4px; padding-bottom:4px; color: #222">`+ displayToDecimals(timeToCompile * 1000, 2) +` ms </td><td style="color: #999">Time To Compile Data</td>
                    </tr>

                    <tr>
                        <td style="padding-left:0px; padding-right:22px; padding-top:4px; padding-bottom:4px; color: #222">`+ displayToDecimals(timeToResponse * 1000, 2) +` ms </td><td style="color: #999">Request Time</td>
                    </tr>

                    <tr>
                        <td style="padding-left:0px; padding-right:22px; padding-top:4px; padding-bottom:4px; color: #222">`+ displayToDecimals(timeTotal * 1000, 2) +` ms </td><td style="color: #999">Total Time</td>
                    </tr>
                    </table>


                    <div style="margin-top:22px">
                        <h3 style="margin:0 0 7px 0;">Immediate Paths</h3>
                        <table>
                        `+ immediatePaths +`
                        </table>

                        <h3 style="margin:33px 0 7px 0;">Throttled Paths</h3>
                        <table>
                        `+ throttledPaths +`
                        </table>

                    </div>

                </div>
                `);
                parent.append(display)
            }
        })
        return false;
    })
</script>

<?php endif ?>
