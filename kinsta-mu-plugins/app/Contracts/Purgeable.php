<?php

namespace Kinsta\KMP\Contracts;

interface Purgeable extends Nameable
{
	/**
	 * Clear (purge) something.
	 *
	 * This is usually cache, but can also be anything that needs to be cleared.
	 */
	public function purge(): void;
}
