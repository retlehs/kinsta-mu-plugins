<?php

namespace Kinsta\KMP\Cache\Autopurge;

use WC_Product;

use function class_exists;
use function Kinsta\KMP\debug_log;
use function wc_get_product;

final class WooCommerceController extends Controller
{
	protected string $name = 'woocommerce_controller';

	public function hook(): void
	{
		add_action('woocommerce_product_set_stock', [$this, 'onStockChange'], 10, 1);
		add_action('woocommerce_variation_set_stock', [$this, 'onStockChange'], 10, 1);
		add_action('woocommerce_product_set_stock_status', [$this, 'onStockStatusChange'], 10, 3);
		add_action('woocommerce_variation_set_stock_status', [$this, 'onStockStatusChange'], 10, 3);
	}

	/**
	 * Fires when a product's stock quantity is changed, either by a manual
	 * update or as a result of an order being placed.
	 */
	public function onStockChange(WC_Product $product): void
	{
		$this->purgeProductCache($product);

		debug_log(
			sprintf(
				'Stock quantity changed for product ID %d, cache purge initiated.',
				$product->get_id(),
			),
			[
				'controller' => __METHOD__,
				'product_id' => $product->get_id(),
			],
		);
	}

	/**
	 * Fires when a product's stock status (in stock / out of stock) changes.
	 *
	 * @param int             $productId   Product or variation ID.
	 * @param string          $stockStatus New stock status.
	 * @param WC_Product|null $product     Product instance, when available.
	 */
	public function onStockStatusChange(int $productId, string $stockStatus, ?WC_Product $product = null): void
	{
		if (! $product instanceof WC_Product) {
			$product = wc_get_product($productId);
		}

		if (! $product instanceof WC_Product) {
			return;
		}

		$this->purgeProductCache($product);

		debug_log(
			sprintf(
				'Stock status changed for product ID %d to "%s", cache purge initiated.',
				$productId,
				$stockStatus,
			),
			[
				'controller' => __METHOD__,
				'product_id' => $productId,
				'stock_status' => $stockStatus,
			],
		);
	}

	public function isSupported(): bool
	{
		return class_exists('WooCommerce');
	}

	public function getDescription(): string
	{
		return __('Purge cache when WooCommerce product stock changes, including manual updates and new sales.', 'kinsta-mu-plugins');
	}

	private function purgeProductCache(WC_Product $product): void
	{
		// Avoid multiple purges on the same request and if autopurge is disabled.
		if (! $this->shouldProceed() || ! $this->isOn()) {
			return;
		}

		// Variations don't have their own front-end URL; purge the parent product page instead.
		$postId = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

		if ($postId <= 0 || ! $this->isPostPublished($postId)) {
			return;
		}

		$this->kmp->kinsta_cache_purge->purge_single_happened = true;
		$this->kmp->kinsta_cache_purge->initiate_purge($postId);
	}

	private function isPostPublished(int $postId): bool
	{
		return ! wp_is_post_autosave($postId) && ! wp_is_post_revision($postId) && get_post_status($postId) === 'publish';
	}
}
