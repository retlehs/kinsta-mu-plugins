<?php

namespace Kinsta\KMP\Contracts;

interface Clearable extends Nameable
{
	/**
	 * Clear (purge) something.
	 *
	 * This is usually cache, but can also be anything that needs to be cleared.
	 */
	public function clear(): void;
}
