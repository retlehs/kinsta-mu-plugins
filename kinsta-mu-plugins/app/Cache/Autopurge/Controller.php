<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;
use Kinsta\KMP\Contracts\Autopurgable;

use function Kinsta\KMP\debug_log;
use function Kinsta\KMP\is_autopurge_enabled;

abstract class Controller implements Autopurgable
{
	protected KMP $kmp;

    /**
     * @var bool|null|string Set the behavior of the autopurge.
     */
    protected $status = null;

    /**
     * Default value.
     *
     * @var boolean|null
     */
    protected ?bool $default = null;

    /**
     * Unique name of the autopurge controller.
     */
    protected string $name;

    public function __construct(KMP $kmp)
    {
        $this->kmp = $kmp;

        /**
         * Filter to control whether autopurge is enabled for ACF updates.
         *
         * @param bool|null|string $status Whether to enable the option update autopurge. Default `null`.
         * @param string $name The name of the autopurge controller..
         */
        $this->status = apply_filters('kinsta/kmp/cache/autopurge', $this->status, $this->name);

        /**
         * Force the value from the filter into the option as it should take precedence
         * over the value stored in the option table.
         */
        add_filter('option_kinsta_kmp_cache_autopurge', function($value) {
            if ($this->status !== null && is_bool($this->status)) {
                $value[$this->name] = $this->status;
            }

            return $value;
        }, PHP_INT_MAX);
    }

    public function clear(): void
	{
        if (! $this->isOn()) {
            debug_log('Autopurge is disabled, skipping cache clear.', ['controller' => $this->name]);

            return;
        }

        if (! $this->kmp->kinsta_cache_purge instanceof Cache_Purge) {
            return;
        }

        $this->kmp->kinsta_cache_purge->purge_complete_caches();

        debug_log('All caches were cleared.', ['controller' => $this->name]);
	}

    public function isOn(): bool
    {
        if (! is_autopurge_enabled()) {
            return false;
        }

        /**
         * The filter should take precedence over the option setting. If the filter
         * explicitly return `false`, do not proceed with the cache purge.
         */
        if ($this->status === false) {
            return false;
        }

        $options = get_option( 'kinsta_kmp_cache_autopurge' );
        $value = $options[$this->name] ?? $this->default ?? null;

        /**
         * When the value is `null`, it means the option is not set, so we should
         * treat it as if it is enabled.
         */
        return in_array( $value, [true, 1, '1', null], true );
    }

    public function isSupported(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefault(): ?bool
    {
        return $this->default;
    }
}
