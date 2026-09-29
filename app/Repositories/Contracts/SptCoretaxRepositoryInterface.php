<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SptCoretaxRepositoryInterface
{
    public function searchSpt(array $filters, int $perPage = 25): LengthAwarePaginator;

    public function getDistinctJenisSpt(): Collection;

    public function getDistinctStatusSpt(): Collection;

    public function getDistinctAR(): Collection;
}
