<?php

namespace Kinsta\KMP\Contracts;

interface Autopurgable extends Nameable, Describable, Clearable, Hookable
{
    /**
     * Whether should perform clearing (purging).
     *
     * @return bool True if on/enabled, false otherwise.
     */
    public function isOn(): bool;

    /**
     * Whether this feature is supported in the current site/setup.
     *
     * @return bool True if supported, false otherwise.
     */
    public function isSupported(): bool;

    /**
     * Retrieve the default setting.
     *
     * @return bool|null
     */
    public function getDefault(): ?bool;
}
