<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

class ReportService
{
    public function aggregate(?string $startDate, ?string $endDate, string $groupBy = 'day', ?int $userId = null): array
    {
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(30);
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now();

        [$format, $labelResolver] = $this->groupingFormat($groupBy);

        $baseQuery = Transaction::query()
            ->whereBetween('transactions.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if ($userId) {
            $baseQuery->where('user_id', $userId);
        }

        $records = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(transactions.created_at, '{$format}') as period_key")
            ->selectRaw('SUM(transactions.total) as sales_total')
            ->selectRaw('SUM(transactions.profit) as profit_total')
            ->selectRaw('COUNT(*) as transactions_count')
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $data = $records->map(function ($record) use ($groupBy, $labelResolver) {
            $label = $labelResolver($record->period_key);

            return [
                'period' => $record->period_key,
                'label' => $label,
                'sales' => (float) $record->sales_total,
                'profit' => (float) $record->profit_total,
                'transactions' => (int) $record->transactions_count,
            ];
        });

        $cashiers = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw('transactions.user_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw('SUM(transactions.total) as sales_total')
            ->selectRaw('SUM(transactions.profit) as profit_total')
            ->selectRaw('COUNT(*) as transactions_count')
            ->groupBy('transactions.user_id', 'users.name')
            ->orderByDesc('sales_total')
            ->get()
            ->map(static function ($record) {
                return [
                    'user_id' => (int) $record->user_id,
                    'name' => $record->user_name,
                    'sales' => (float) $record->sales_total,
                    'profit' => (float) $record->profit_total,
                    'transactions' => (int) $record->transactions_count,
                ];
            });

        return [
            'range' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'group_by' => $groupBy,
            ],
            'summary' => [
                'sales' => (float) $data->sum('sales'),
                'profit' => (float) $data->sum('profit'),
                'transactions' => (int) $data->sum('transactions'),
            ],
            'data' => $data->values()->all(),
            'cashiers' => $cashiers->values()->all(),
        ];
    }


    protected function groupingFormat(string $groupBy): array
    {
        return match ($groupBy) {
            'week' => [
                '%x-%v',
                function (string $period) {
                    [$year, $week] = explode('-', $period);
                    $start = Carbon::now()->setISODate((int) $year, (int) $week)->startOfWeek();
                    $end = $start->copy()->endOfWeek();

                    return $start->format('d M') . ' - ' . $end->format('d M');
                },
            ],
            'month' => [
                '%Y-%m',
                fn (string $period) => Carbon::createFromFormat('Y-m', $period)->format('M Y'),
            ],
            default => [
                '%Y-%m-%d',
                fn (string $period) => Carbon::createFromFormat('Y-m-d', $period)->format('d M'),
            ],
        };
    }
}
