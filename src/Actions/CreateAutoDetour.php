<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Arr;
use JustBetter\Detour\Contracts\CreatesAutoDetour;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;

class CreateAutoDetour implements CreatesAutoDetour
{
    public function __construct(
        protected FindsDetour $findContract,
        protected StoresDetour $storeContract,
        protected DeletesDetour $deleteContract,
        protected ResolvesRepository $resolvesRepository,
    ) {}

    public function create(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        // The new URI is live now, so a detour away from it would shadow the page.
        // This also removes detours that repointing would turn into a loop.
        $this->deleteWhere('from', $to);

        // A single detour per source URI; the latest move wins.
        $this->deleteWhere('from', $from);

        $this->repointChains($from, $to);

        $this->storeContract->store(Form::make([
            'from' => $from,
            'to' => $to,
            'code' => '301',
            'type' => Type::Path->value,
        ]));
    }

    protected function repointChains(string $from, string $to): void
    {
        $repository = $this->resolvesRepository->resolve();

        $this->findContract
            ->where('to', $from)
            ->filter(fn (Detour $detour): bool => $detour->isType(Type::Path))
            ->each(fn (Detour $detour) => $repository->update($detour->id, Form::make([
                ...Arr::except($detour->toArray(), ['created_at', 'updated_at']),
                'to' => $to,
            ])));
    }

    protected function deleteWhere(string $field, string $value): void
    {
        $this->findContract
            ->where($field, $value)
            ->filter(fn (Detour $detour): bool => $detour->isType(Type::Path))
            ->each(fn (Detour $detour) => $this->deleteContract->delete($detour->id));
    }

    public static function bind(): void
    {
        app()->singleton(CreatesAutoDetour::class, static::class);
    }
}
