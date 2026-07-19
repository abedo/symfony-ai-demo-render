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

final class Activity
{
    /**
     * @var string Time of the activity (e.g., "Morning", "Afternoon", "Evening")
     */
    #[With(enum: ['Morning', 'Afternoon', 'Evening'])]
    public string $time;

    /**
     * @var string Description of the activity
     */
    public string $description;

    /**
     * @var string Estimated cost of the activity (e.g., "Free", "$20", "Included")
     */
    public string $cost;

    public function toString(): string
    {
        return \sprintf('- %s: %s (Cost: %s)', $this->time, $this->description, $this->cost);
    }
}
