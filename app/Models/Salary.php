<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'base_salary',
        'bonuses',
        'deductions',
        'net_salary',
        'month',
        'year',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_salary' => 'float',
            'bonuses' => 'float',
            'deductions' => 'float',
            'net_salary' => 'float',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForMonth(Builder $query, null|int|string $month): Builder
    {
        if (blank($month)) {
            return $query;
        }

        return $query->where('month', $month);
    }

    public function scopeForYear(Builder $query, null|int|string $year): Builder
    {
        if (blank($year)) {
            return $query;
        }

        return $query->where('year', $year);
    }

    public static function calculateNetSalary(float $baseSalary, float $bonuses, float $deductions): float
    {
        return $baseSalary + $bonuses - $deductions;
    }
}
