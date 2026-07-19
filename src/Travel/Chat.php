<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Travel;

use App\Travel\Data\Itinerary;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

final class Chat
{
    private const SESSION_KEY = 'travel-chat';

    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'ai.agent.travel')]
        private readonly AgentInterface $agent,
    ) {
    }

    public function getItinerary(): Itinerary
    {
        $messages = $this->loadMessages()->getMessages();

        if (0 === \count($messages)) {
            throw new \RuntimeException('No itinerary generated yet. Please submit a message first.');
        }

        $message = $messages[\count($messages) - 1];

        if (!$message->getMetadata()->has('itinerary')) {
            throw new \RuntimeException('The last message does not contain an itinerary.');
        }

        return $message->getMetadata()->get('itinerary');
    }

    public function submitMessage(string $message): void
    {
        $messages = $this->loadMessages();

        $messages->add(Message::ofUser($message));
        $result = $this->agent->call($messages, ['response_format' => Itinerary::class]);

        \assert($result instanceof ObjectResult);

        $itinerary = $result->getContent();

        \assert($itinerary instanceof Itinerary);

        $assistantMessage = Message::ofAssistant($itinerary->toString());
        $assistantMessage->getMetadata()->add('itinerary', $result->getContent());
        $messages->add($assistantMessage);

        $this->saveMessages($messages);
    }

    public function reset(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    private function loadMessages(): MessageBag
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, new MessageBag());
    }

    private function saveMessages(MessageBag $messages): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $messages);
    }
}
