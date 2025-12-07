<?php

namespace Kinsta\KMP\Cache;

use ArrayAccess;
use IteratorAggregate;
use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Contracts\Autopurgable;
use Kinsta\KMP\Contracts\Hookable;
use PHPUnit\Runner\Hook;
use RuntimeException;
use Traversable;

/**
 * Manage the Autopurge controllers.
 */
final class Autopurge implements ArrayAccess, IteratorAggregate, Hookable
{
    /**
     * @var array<string,Autopurgable> List of registered autopurge controllers.
     */
    private array $autopurgables = [];

    public function add(Autopurgable ...$autopurgables): void
    {
        foreach ($autopurgables as $autopurgable) {
            /**
             * If the autopurge controller is not supported whether it is because the
             * feature required is not available or is not enabled on the site, or
             * the required plugin is not active, then it should not be included
             * on the list.
             *
             * When the autopurge controller is not on the list, it also won't be
             * available on the settings page or appears on the list on WP-CLI.
             */
            if (! $autopurgable->isSupported()) {
                continue;
            }

            $name = $autopurgable->getName();

            /**
             * Avoid adding the same autopurge controller more than once.
             */
            if (isset($this->autopurgables[$name])) {
                continue;
            }

            if ($autopurgable instanceof Hookable) {
                $autopurgable->hook();
            }

            $this->autopurgables[$name] = $autopurgable;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        if (!is_string($offset) || $offset === '') {
            return false;
        }

        return isset($this->autopurgables[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetGet($offset): ?Autopurgable
    {
        if (!is_string($offset) || $offset === '') {
            return null;
        }

        return $this->autopurgables[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Not supported');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Not supported');
    }

    public function getIterator(): Traversable
    {
        yield from $this->autopurgables;
    }

	public function hook(): void
	{
        add_filter('default_option_kinsta-autopurge-status', fn () => null, PHP_INT_MAX);
        add_filter('default_option_kinsta_kmp_cache_autopurge', function () {
            $defaults = [];
            foreach ($this->autopurgables as $key => $autopurgable) {
                $defaults[$key] = $autopurgable->getDefault();
            }

            return $defaults;
        }, PHP_INT_MAX);
	}

    public function update(string $key, bool $status): bool
    {
        if (!array_key_exists($key, $this->autopurgables)) {
            throw new RuntimeException(sprintf('The autopurge controller with key "%s" does not exist.', $key));
        }

        $options = get_option('kinsta_kmp_cache_autopurge');
        $options[$key] = $status;

        return update_option('kinsta_kmp_cache_autopurge', $options);
    }

    public function status(): ?string
    {
        return get_option('kinsta-autopurge-status');
    }

    public function disable(): bool
    {
        return (bool) update_option('kinsta-autopurge-status', 'disabled');
    }

    public function enable(): bool
    {
        return (bool) update_option('kinsta-autopurge-status', 'enabled');
    }
}
