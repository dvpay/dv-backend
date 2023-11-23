<?php

namespace App\Support\Macros;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CreateUpdateOrDelete
{
    protected HasMany $query;
    protected Collection $records;
    protected string $recordKeyName;

    public function __construct(HasMany $query, iterable $records, string $recordKeyName = null)
    {
        $this->query = $query;
        $this->records = collect($records);
        $this->recordKeyName = $recordKeyName ?? $query->getRelated()->getKeyName();
    }

    public function __invoke(): void
    {
        DB::transaction(function () {
            $this->deleteMissingRecords();
            $this->updateOrCreateRecords();
        });
    }

    protected function deleteMissingRecords(): void
    {
        $recordKeyName = $this->recordKeyName;
        $existingRecordIds = $this->records
            ->pluck($recordKeyName)
            ->filter();

        (clone $this->query)
            ->whereNotIn($recordKeyName, $existingRecordIds)
            ->delete();
    }

    protected function updateOrCreateRecords(): void
    {
        $recordKeyName = $this->recordKeyName;
        $this->records->each(function ($record) use ($recordKeyName) {
            (clone $this->query)->updateOrCreate([
                $recordKeyName => $record[$recordKeyName] ?? null,
            ], $record);
        });
    }
}