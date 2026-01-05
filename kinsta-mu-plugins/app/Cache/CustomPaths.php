<?php

namespace Kinsta\KMP\Cache;

use function array_values;
use function count;
use function is_array;
use function preg_replace;
use function substr;
use function trim;

/**
 * The controller that manages custom paths for cache purging.
 */
class CustomPaths
{
	/**
	 * The name of the option where additional paths are stored.
	 */
	public const OPTION_NAME = 'kinsta-cache-additional-paths';

	/**
	 * Update or add the custom paths.
	 *
	 * @param array<"path"|"type",string> $new The new path data to be added or updated.
	 */
	public function update(array $new): bool
	{
		$paths = (array) get_option(self::OPTION_NAME, []);

		if ($new === [] || $new['path'] === '' || $new['type'] === '') {
			return false;
		}

		if ($new['type'] !== 'single' && $new['type'] !== 'group') {
			return false;
		}

		$paths[] = [
			'path' => sanitize_text_field(self::sanitizePath($new['path'])),
			'type' => sanitize_text_field($new['type']),
		];

		return update_option(self::OPTION_NAME, array_values($paths));
	}

	/**
	 * Retrieve the custom paths.
	 *
	 * @return array<int,array<"path"|"type",string>> An array of paths with their types.
	 */
	public function get(): array
	{
		$data = get_option(self::OPTION_NAME, []);

		if (! is_array($data)) {
			$data = [];
		}

		return $data;
	}

	public function remove(int $index): bool
	{
		$paths = $this->get();

		if (isset($paths[$index]) && ! empty($paths[$index])) {
			unset($paths[$index]);
		}

		if (count($paths) === 0) {
			return delete_option('kinsta-cache-additional-paths');
		}

		return update_option('kinsta-cache-additional-paths', array_values($paths));
	}

	private static function sanitizePath(string $input): string
	{
		$path = trim($input);
		$path = preg_replace('/^(\.\/+|\.\.\/+)/', '', $path);
		$path = preg_replace('/^\/+|\/+$/', '', $path);
		$path = preg_replace('/\/{2,}/', '/', $path);

		if (substr($input, -1) === '/' && $path !== '') {
			$path .= '/';
		}

		return $path;
	}
}
