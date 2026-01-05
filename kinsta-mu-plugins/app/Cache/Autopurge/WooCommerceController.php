<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;

final class WooCommerceController extends Controller
{
    protected string $name = 'woocommerce_controller';

	public function hook(): void
	{
        add_action('woocommerce_product_set_stock', [$this, 'onStockChange'], 10, 1);
	}

    public function onStockChange(\WC_Product $product): void
    {
        if ( ! $this->kmp->kinsta_cache_purge instanceof Cache_Purge ) {
            return;
        }

        // Avoid multiple purges on the same request and if autopurge is disabled.
        if ( $this->kmp->kinsta_cache_purge->purge_single_happened || ! $this->isOn() ) {
			return;
		}

        $postId = $product->get_id();

		if ( $this->isPostPublished( $postId ) ) {
			$this->kmp->kinsta_cache_purge->purge_single_happened = true;
			$this->kmp->kinsta_cache_purge->initiate_purge( $postId );
		}
    }

    public function isSupported(): bool
    {
        return class_exists( 'WooCommerce' );
    }

    public function getDescription(): string
    {
        return __('Purge cache when WooCommerce product details (e.g. stocks) are updated.', 'kinsta-mu-plugins');
    }

    private function isPostPublished(int $postId): bool
    {
        return ! wp_is_post_autosave( $postId ) && ! wp_is_post_revision( $postId ) && 'publish' === get_post_status( $postId );
    }
}
