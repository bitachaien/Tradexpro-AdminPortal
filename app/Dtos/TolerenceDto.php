<?php

namespace App\Dtos;

class TolerenceDto
{
    public function __construct(
        public $low_tolerence,
        public $high_tolerence,
    ) {}
}
