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

use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;

final class Itinerary
{
    /**
     * @var string Name of the destination
     */
    public string $destination;

    /**
     * @var int Total duration in days
     */
    #[With(minimum: 1, maximum: 14)]
    public int $durationInDays;

    /**
     * @var string Budget category
     */
    #[With(enum: ['Budget', 'Moderate', 'Luxury'])]
    public string $budget;

    /**
     * @var string An exciting, brief summary of the itinerary
     */
    public string $description;

    /**
     * @var string[] Top attractions or highlights of the trip
     */
    public array $highlights;

    /**
     * @var DayPlan[] Day-by-day breakdown of the itinerary
     */
    public array $days;

    public function toString(): string
    {
        $highlightsStr = implode(', ', $this->highlights);
        $daysStr = implode(\PHP_EOL.\PHP_EOL, array_map(static fn (DayPlan $day) => $day->toString(), $this->days));

        return <<<ITINERARY
            Destination: {$this->destination}
            Duration: {$this->durationInDays} days
            Budget: {$this->budget}
            Description: {$this->description}
            Highlights: {$highlightsStr}

            Itinerary:
            {$daysStr}
            ITINERARY;
    }
}
