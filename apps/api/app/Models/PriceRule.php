<?php

namespace App\Models;

use Database\Factories\PriceRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A peak or off-peak price (FR-10): on these business days, between these times,
 * this price per hour, for one court or for all courts (court_id null).
 *
 * @property int|null $court_id
 * @property string|null $name
 * @property list<int> $weekdays 0 = Sunday ... 6 = Saturday
 * @property string $starts_at "18:00:00"
 * @property string $ends_at "00:00:00"
 * @property bool $ends_next_day
 * @property int $price_sen per hour
 */
#[Fillable(['court_id', 'name', 'weekdays', 'starts_at', 'ends_at', 'ends_next_day', 'price_sen'])]
class PriceRule extends Model
{
    /** @use HasFactory<PriceRuleFactory> */
    use HasFactory;

    private const SHORT_DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    /** Monday first, the way people read a week. */
    private const WEEK_ORDER = [1, 2, 3, 4, 5, 6, 0];

    protected function casts(): array
    {
        return [
            'court_id' => 'integer',
            'weekdays' => 'array',
            'ends_next_day' => 'boolean',
            'price_sen' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /** Minutes after its business day's midnight when the rule starts, e.g. 18:00 -> 1080. */
    public function startMinute(): int
    {
        return self::minutes($this->starts_at);
    }

    /** Minutes after its business day's midnight when it ends: past 1440 when it ends after midnight. */
    public function endMinute(): int
    {
        return self::minutes($this->ends_at) + ($this->ends_next_day ? 1440 : 0);
    }

    /** Does the rule cover this half hour? $minute counts from the business day's midnight (ADR-015). */
    public function covers(int $weekday, int $minute): bool
    {
        return in_array($weekday, $this->weekdays, true)
            && $minute >= $this->startMinute()
            && $minute < $this->endMinute();
    }

    /**
     * ADR-020: two rules for the same courts may not cover the same half hour, otherwise
     * nobody could tell which price wins. Touching is fine: 6 pm – 8 pm and 8 pm – midnight.
     * A rule for one court and a rule for all courts never clash: the court's own rule wins.
     */
    public function clashesWith(PriceRule $other): bool
    {
        return $this->court_id === $other->court_id
            && array_intersect($this->weekdays, $other->weekdays) !== []
            && $this->startMinute() < $other->endMinute()
            && $other->startMinute() < $this->endMinute();
    }

    /** For messages: "Weekday peak (Mon–Fri, 6:00 pm – midnight)". */
    public function describe(): string
    {
        $endsAtMidnight = $this->ends_next_day && self::minutes($this->ends_at) === 0;
        $until = $endsAtMidnight
            ? 'midnight'
            : self::formatTime($this->ends_at).($this->ends_next_day ? ' (next day)' : '');
        $when = self::formatWeekdays($this->weekdays).', '.self::formatTime($this->starts_at).' – '.$until;

        return $this->name === null ? $when : "{$this->name} ({$when})";
    }

    /** "18:00" or "18:00:00" -> 1080 */
    public static function minutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);

        return (int) $hours * 60 + (int) $minutes;
    }

    /**
     * [1, 2, 3, 4, 5] -> "Mon–Fri", [6, 0] -> "Sat, Sun", all seven -> "Every day".
     *
     * @param  list<int>  $weekdays
     */
    public static function formatWeekdays(array $weekdays): string
    {
        if (count(array_unique($weekdays)) === 7) {
            return 'Every day';
        }

        // Group days that follow each other (Monday first), then write 3 or more as a range.
        $runs = [];
        $previous = null;
        foreach (self::WEEK_ORDER as $position => $day) {
            if (! in_array($day, $weekdays, true)) {
                continue;
            }
            if ($previous !== null && $previous === $position - 1) {
                $runs[array_key_last($runs)][] = $day;
            } else {
                $runs[] = [$day];
            }
            $previous = $position;
        }

        $parts = [];
        foreach ($runs as $run) {
            $names = array_map(fn (int $day) => self::SHORT_DAY_NAMES[$day], $run);
            $parts[] = count($names) >= 3 ? $names[0].'–'.$names[count($names) - 1] : implode(', ', $names);
        }

        return implode(', ', $parts);
    }

    /** "18:00" -> "6:00 pm", the same as the web's formatTime(). */
    private static function formatTime(string $time): string
    {
        $minutes = self::minutes($time);
        $hours = intdiv($minutes, 60);

        return sprintf('%d:%02d %s', $hours % 12 === 0 ? 12 : $hours % 12, $minutes % 60, $hours < 12 ? 'am' : 'pm');
    }
}
