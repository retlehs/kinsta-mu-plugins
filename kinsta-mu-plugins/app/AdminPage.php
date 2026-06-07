<?php

namespace Kinsta\KMP;

use Kinsta\KMP\Helpers\Whitelabel;

use function array_merge;
use function array_values;
use function strpos;

class AdminPage
{
	private Whitelabel $whitelabel;

	/** @var array<string,string> */
	private array $menuSlugs;

	public function __construct(Whitelabel $whitelabel)
	{
		$this->whitelabel = $whitelabel;
		$this->menuSlugs = [
			'tools' => $this->whitelabel->getMenuKey('tools'), // e.g. kinsta-tools, server-tools, [key]-tools
			'cdn' => $this->whitelabel->getMenuKey('cdn'), // e.g. kinsta-cdn, server-cdn, [key]-cdn
			'settings' => $this->whitelabel->getMenuKey('settings'), // e.g. kinsta-settings, server-settings, [key]-settings
		];
	}

	/**
	 * Retrieve the menu slug of the plugin admin page.
	 *
	 * @param string $name The name of the menu slug to retrieve (e.g. 'tools', 'cdn', 'settings').
	 */
	public function getMenuKey(string $name): ?string
	{
		return $this->menuSlugs[$name] ?? null;
	}

	/**
	 * Retrieve the menu title of the plugin admin page.
	 */
	public function getMenuTitle(): string
	{
		return $this->whitelabel->getMenuTitle();
	}

	/**
	 * Retrieve the menu icon of the plugin admin page.
	 */
	public function getMenuIcon(): ?string
	{
		return $this->whitelabel->getMenuIcon();
	}

	/**
	 * Check whether page is the plugin admin page.
	 */
	public function isPluginPage(): bool
	{
		if (! is_admin()) {
			return false;
		}

		$page = $_GET['page'] ?? '';
		$menuSlugs = array_merge(
			['kinsta-tools', 'kinsta-cdn', 'kinsta-settings'],
			array_values($this->menuSlugs),
		);

		$isPluginPage = false;
		foreach ($menuSlugs as $slug) {
			if ($slug !== '' && strpos((string) $page, $slug) !== false) {
				$isPluginPage = true;
				break;
			}
		}

		return $isPluginPage;
	}
}
