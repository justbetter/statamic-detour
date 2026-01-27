<?php

namespace JustBetter\Detour\Data;

use JustBetter\Detour\Enums\Type;

/**
 * @property string $id
 * @property string $from
 * @property string $to
 * @property string $type
 * @property int $code
 * @property array<int, string> $sites
 *
 * @extends Data<string, mixed>
 */
class Detour extends Data
{
    public function matches(string $site, string $path): bool
    {
        $match = match ($this->type()) {
            Type::Path => $this->from === $path,
            Type::Regex => (bool) preg_match($this->from, $path),
        };

        return $match && (empty($this->sites) || in_array($site, $this->sites));
    }

    public function type(): Type
    {
        return Type::from($this->type);
    }

    public function isType(Type $type): bool
    {
        return $this->type() === $type;
    }
}
