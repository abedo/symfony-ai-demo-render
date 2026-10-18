<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Travel;

use App\Travel\Data\Itinerary;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Agent\Execution\Execution;
use Symfony\AI\Agent\Execution\Update\Result as ResultUpdate;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Result\ObjectResult;

/**
 * The MockAgent only answers with text, so this agent returns the structured itinerary instead.
 */
final class StubAgent implements AgentInterface
{
    /**
     * @var list<array<string, mixed>>
     */
    public array $calls = [];

    public function __construct(
        private readonly Itinerary $itinerary,
    ) {
    }

    public function call(string|MessageBag|UserMessage $input, array $options = []): Execution
    {
        $this->calls[] = $options;
        $result = new ObjectResult($this->itinerary);

        return new Execution(static function () use ($result): \Generator {
            yield new ResultUpdate($result);
        });
    }

    public function getName(): string
    {
        return 'travel';
    }
}
