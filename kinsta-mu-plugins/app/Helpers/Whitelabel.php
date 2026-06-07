<?php

namespace Kinsta\KMP\Helpers;

use function is_array;
use function is_bool;
use function is_string;
use function trim;

class Whitelabel
{
	/** @var bool|array{"menu_key":string,"menu_title":string,"menu_icon":?string} */
	private $values = false;

	/** @param bool|array{"menu_key":string,"menu_title":string,"menu_icon":?string} $values */
	public function __construct($values = false)
	{
		$this->values = $values;
	}

	public function getMenuKey(?string $name = null): string
	{
		if (! $this->isEnabled()) {
			return $this->getDefaultMenuKey($name);
		}

		if (is_string($name) && trim($name) !== '') {
			$name = '-' . sanitize_key($name);
		}

		if (! is_array($this->values)) {
			return 'server' . $name;
		}

		$menuKey = $this->values['menu_key'];

		if (trim($menuKey) !== '') {
			$menuKey = sanitize_key($menuKey);
		}

		return $menuKey . $name;
	}

	private function getDefaultMenuKey(?string $name = null): string
	{
		if (! $name || ! is_string($name)) {
			return 'kinsta';
		}

		return 'kinsta-' . sanitize_key($name);
	}

	public function getMenuTitle(): string
	{
		if (! $this->isEnabled()) {
			return __('Kinsta Cache', 'kinsta-mu-plugins');
		}

		if (! is_array($this->values)) {
			return __('Server Cache', 'kinsta-mu-plugins');
		}

		$menuLabel = $this->values['menu_title'];

		if (trim($menuLabel) !== '') {
			return sanitize_text_field($menuLabel);
		}

		return __('Server Cache', 'kinsta-mu-plugins');
	}

	/**
	 * Override the default menu icon if provided in the configuration.
	 * Otherwise, just use cloud icon as the default for the server
	 * cache menu.
	 */
	public function getMenuIcon(): ?string
	{
		if (is_array($this->values)) {
			$customIcon = $this->values['menu_icon'] ?? null;

			if (is_string($customIcon) && trim($customIcon) !== '') {
				return sanitize_text_field($customIcon);
			}
		}

		return 'dashicons-cloud';
	}

	public function isEnabled(): bool
	{
		if (is_bool($this->values)) {
			return $this->values;
		}

		return trim($this->values['menu_key']) !== '' && trim($this->values['menu_title']) !== '';
	}
}
