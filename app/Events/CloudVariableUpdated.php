<?php declare(strict_types=1);

namespace App\Events;

use App\Models\CloudVariable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CloudVariableUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>|null  $oldValue
     * @param  array<string, mixed>|null  $newValue
     */
    public function __construct(
        public CloudVariable $cloudVariable,
        public ?array $oldValue,
        public ?array $newValue,
    ) {}
}
