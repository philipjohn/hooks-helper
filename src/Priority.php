<?php

namespace PJ\HooksHelper;

use Attribute;

#[Attribute]
class Priority {

    /**
     * @param int $value The priority value for the hook.
     *                   Lower numbers correspond to earlier execution.
     */
    public function __construct(
        public int $value = 10
    ) {}

}