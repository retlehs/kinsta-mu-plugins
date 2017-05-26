<div class='kinsta-widget'>
    <div class="kinsta-dropdown kinsta-dropdown__full">
        <button class='kinsta-button kinsta-button__full kinsta-button__large'><?php _e( 'Clear Caches', 'kinsta-cache' ) ?></button>

        <div class="kinsta-dropdown-content">
          <button data-nonce='<?php echo wp_create_nonce( 'kinsta-clear-cache-all' ) ?>' data-action='kinsta_clear_cache_all' class='kinsta-clear-cache kinsta-button kinsta-button__white kinsta-button__small kinsta-button__full-left kinsta-loader' data-progressText='<?php _e( 'Clearing Caches...', 'kinsta-cache' ) ?>'  data-completedText='<?php _e( 'Caches Cleared', 'kinsta-cache' ) ?>'><?php _e( 'Clear All Caches', 'kinsta-cache' ) ?></button>

          <button data-nonce='<?php echo wp_create_nonce( 'kinsta-clear-cache-full-page' ) ?>' data-action='kinsta_clear_cache_full_page' class='kinsta-clear-cache kinsta-button kinsta-button__white kinsta-button__small kinsta-button__full-left kinsta-loader' data-progressText='<?php _e( 'Clearing Full Page Cache...', 'kinsta-cache' ) ?>'  data-completedText='<?php _e( 'Cache Cleared', 'kinsta-cache' ) ?>'><?php _e( 'Clear Full Page Cache Only', 'kinsta-cache' ) ?></button>

          <button data-nonce='<?php echo wp_create_nonce( 'kinsta-clear-cache-object' ) ?>' data-action='kinsta_clear_cache_object' class='kinsta-clear-cache kinsta-button kinsta-button__white kinsta-button__small kinsta-button__full-left kinsta-loader' data-progressText='<?php _e( 'Clearing Object Cache...', 'kinsta-cache' ) ?>'  data-completedText='<?php _e( 'Cache Cleared', 'kinsta-cache' ) ?>'><?php _e( 'Clear Object Cache Only', 'kinsta-cache' ) ?></button>

      </div>
    </div>
</div>
