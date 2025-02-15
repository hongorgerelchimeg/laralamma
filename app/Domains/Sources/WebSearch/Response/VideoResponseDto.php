<?php

namespace App\Domains\Sources\WebSearch\Response;

use Spatie\LaravelData\Data;

class VideoResponseDto extends Data
{
    public function __construct(
        public string $url,
        public string $title,
        public string $description,
        public string $age,
        public array $meta_data,
        public string $thumbnail,
    ) {
    }
}
