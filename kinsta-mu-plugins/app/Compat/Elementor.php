<?php

namespace Kinsta\KMP\Compat;

use Kinsta\Cache_Purge;
use Kinsta\KMP;

class Elementor
{
	private KMP $kmp;

	public function __construct(KMP $kmp)
	{
		$this->kmp = $kmp;
		$this->hook();
	}

	private function hook(): void
	{
		add_action('elementor/core/files/clear_cache', [$this, 'clearCache']);
		add_action('elementor/maintenance_mode/mode_changed', [$this, 'clearCache']);
	}

	public function clearCache(): void
	{
		/**
		 * Filter to control whether the Elementor compatibility on Kinsta mu-plugins
		 * should clear the cache. If it returns `false`, it will not clear the cache.
		 */
		if (! apply_filters('kinsta/kmp/compat/elementor/clear_cache', true)) {
			return;
		}

        /**
         * Ensure `kinsta_cache_purge` is an instance of `Cache_Purge` before clearing teh cache.
         */
        if (!($this->kmp->kinsta_cache_purge instanceof Cache_Purge)) {
			return;
		}

        $this->kmp->kinsta_cache_purge->purge_complete_caches();
	}
}
