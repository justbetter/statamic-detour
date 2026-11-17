<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use JustBetter\Detour\Contracts\CreatesAutoDetour;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Facades\URL;
use Statamic\Sites\Site;

class CreateAutoDetour implements CreatesAutoDetour
{
    public function __construct(
        protected FindsDetour $findContract,
        protected StoresDetour $storeContract,
        protected DeletesDetour $deleteContract,
        protected ResolvesRepository $resolvesRepository,
    ) {}

    public function create(string $fromUri, string $toUri, string $site): void
    {
        /** @var Site $siteInstance */
        $siteInstance = SiteFacade::get($site) ?? SiteFacade::default();

        $from = $this->path($siteInstance, $fromUri);
        $to = $this->path($siteInstance, $toUri);

        if ($from === $to) {
            return;
        }

        $this->deleteWhere('from', $to, $siteInstance);
        $this->deleteWhere('from', $from, $siteInstance);

        $this->repointChains($from, $to, $siteInstance);

        $this->storeContract->store(Form::make([
            'from' => $from,
            'to' => $to,
            'code' => '301',
            'type' => Type::Path->value,
            'sites' => SiteFacade::multiEnabled() ? [$siteInstance->handle()] : [],
        ]));
    }

    protected function path(Site $site, string $uri): string
    {
        return URL::makeRelative(URL::tidy($site->absoluteUrl().'/'.$uri));
    }

    protected function repointChains(string $from, string $to, Site $site): void
    {
        $repository = $this->resolvesRepository->resolve();

        $this->ownedDetours('to', $from, $site)
            ->each(fn (Detour $detour) => $repository->update($detour->id, Form::make([
                ...Arr::except($detour->toArray(), ['created_at', 'updated_at']),
                'to' => $to,
            ])));
    }

    protected function deleteWhere(string $field, string $value, Site $site): void
    {
        $this->ownedDetours($field, $value, $site)
            ->each(fn (Detour $detour) => $this->deleteContract->delete($detour->id));
    }

    /** @return Enumerable<array-key, Detour> */
    protected function ownedDetours(string $field, string $value, Site $site): Enumerable
    {
        return $this->findContract
            ->where($field, $value)
            ->filter(fn (Detour $detour): bool => $detour->isType(Type::Path))
            ->filter(fn (Detour $detour): bool => ! SiteFacade::multiEnabled()
                || array_values($detour->sites ?? []) === [$site->handle()]);
    }

    public static function bind(): void
    {
        app()->singleton(CreatesAutoDetour::class, static::class);
    }
}
