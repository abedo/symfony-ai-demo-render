<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Travel\Data;

final class DayPlan
{
    /**
     * @var int Number of the day (e.g., 1, 2, 3)
     */
    public int $dayNumber;

    /**
     * @var string Theme or main focus of this day
     */
    public string $theme;

    /**
     * @var Activity[] List of planned activities
     */
    public array $activities;

    public function toString(): string
    {
        $activitiesStr = implode(\PHP_EOL, array_map(static fn (Activity $act) => $act->toString(), $this->activities));

        return <<<DAY
            Day {$this->dayNumber}: {$this->theme}
            {$activitiesStr}
            DAY;
    }
}
