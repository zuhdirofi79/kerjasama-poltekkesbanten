<?php

namespace App\Services;

use App\Enums\CooperationExpiryStatus;
use App\Models\Document;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CooperationExpiryService
{
    public function warningDays(): int
    {
        return (int) config('document_classification.expiry_warning_days', 30);
    }

    public function status(?CarbonInterface $endDate, ?CarbonInterface $today = null): ?CooperationExpiryStatus
    {
        if ($endDate === null) {
            return null;
        }

        $today = Carbon::parse($today ?? now())->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $soonLimit = $today->copy()->addDays($this->warningDays());

        if ($end->lt($today)) {
            return CooperationExpiryStatus::Expired;
        }

        if ($end->lte($soonLimit)) {
            return CooperationExpiryStatus::ExpiringSoon;
        }

        return CooperationExpiryStatus::Active;
    }

    public function applyStatusFilter($query, string $status, ?CarbonInterface $today = null)
    {
        $today = Carbon::parse($today ?? now())->startOfDay();
        $soonLimit = $today->copy()->addDays($this->warningDays());

        return match ($status) {
            CooperationExpiryStatus::Active->value => $query
                ->whereNotNull('documents.cooperation_end_date')
                ->whereDate('documents.cooperation_end_date', '>', $soonLimit->toDateString()),
            CooperationExpiryStatus::ExpiringSoon->value => $query
                ->whereNotNull('documents.cooperation_end_date')
                ->whereDate('documents.cooperation_end_date', '>=', $today->toDateString())
                ->whereDate('documents.cooperation_end_date', '<=', $soonLimit->toDateString()),
            CooperationExpiryStatus::Expired->value => $query
                ->whereNotNull('documents.cooperation_end_date')
                ->whereDate('documents.cooperation_end_date', '<', $today->toDateString()),
            'unspecified' => $query->whereNull('documents.cooperation_end_date'),
            default => $query,
        };
    }

    /**
     * @return array{count: int, items: Collection<int, Document>}
     */
    public function bellPayload(int $limit = 8): array
    {
        $today = now()->startOfDay();
        $soonLimit = $today->copy()->addDays($this->warningDays());

        $base = Document::query()
            ->whereNotNull('cooperation_end_date')
            ->whereDate('cooperation_end_date', '<=', $soonLimit->toDateString());

        $count = (clone $base)->count();

        $items = (clone $base)
            ->with(['yearFolder.category.section', 'documentFolder', 'institutionFolder'])
            ->orderBy('cooperation_end_date')
            ->limit($limit)
            ->get();

        return compact('count', 'items');
    }

    public function daysUntilEnd(?CarbonInterface $endDate, ?CarbonInterface $today = null): ?int
    {
        if ($endDate === null) {
            return null;
        }

        $today = Carbon::parse($today ?? now())->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return (int) $today->diffInDays($end, false);
    }
}
