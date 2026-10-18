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

use App\Travel\Chat;
use App\Travel\Data\Activity;
use App\Travel\Data\DayPlan;
use App\Travel\Data\Itinerary;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class ChatTest extends TestCase
{
    public function testGetItineraryThrowsWithoutMessages()
    {
        $chat = self::createChat(self::createAgent());

        $this->expectException(\RuntimeException::class);

        $chat->getItinerary();
    }

    public function testSubmitMessageStoresStructuredItinerary()
    {
        $agent = self::createAgent();
        $chat = self::createChat($agent);

        $chat->submitMessage('3 days in Lisbon');

        $this->assertSame([['response_format' => Itinerary::class]], $agent->calls);

        $itinerary = $chat->getItinerary();
        $this->assertSame('Lisbon', $itinerary->destination);
        $this->assertCount(1, $itinerary->days);
        $this->assertSame('Tram 28', $itinerary->days[0]->activities[0]->description);
    }

    public function testResetForgetsItinerary()
    {
        $chat = self::createChat(self::createAgent());

        $chat->submitMessage('3 days in Lisbon');
        $chat->reset();

        $this->expectException(\RuntimeException::class);

        $chat->getItinerary();
    }

    private static function createAgent(): StubAgent
    {
        return new StubAgent(self::createItinerary());
    }

    private static function createItinerary(): Itinerary
    {
        $activity = new Activity();
        $activity->time = 'Morning';
        $activity->description = 'Tram 28';
        $activity->cost = '$3';

        $day = new DayPlan();
        $day->dayNumber = 1;
        $day->theme = 'Old town';
        $day->activities = [$activity];

        $itinerary = new Itinerary();
        $itinerary->destination = 'Lisbon';
        $itinerary->durationInDays = 3;
        $itinerary->budget = 'Moderate';
        $itinerary->description = 'Hills and tiles.';
        $itinerary->highlights = ['Alfama', 'Belem'];
        $itinerary->days = [$day];

        return $itinerary;
    }

    private static function createChat(AgentInterface $agent): Chat
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();

        $requestStack = new RequestStack();
        $request = new Request();
        $request->setSession($session);
        $requestStack->push($request);

        return new Chat($requestStack, $agent);
    }
}
