<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Magic8BallBot\Tests\Listener;

use OCA\Magic8BallBot\AppInfo\Application;
use OCA\Magic8BallBot\Listener\BotInvokeListener;
use OCA\Talk\Events\BotInvokeEvent;
use OCP\EventDispatcher\Event;
use Test\TestCase;

class BotInvokeListenerTest extends TestCase {
	private const BOT_URL = 'nextcloudapp://' . Application::APP_ID;

	private BotInvokeListener $listener;

	protected function setUp(): void {
		parent::setUp();
		$this->listener = new BotInvokeListener();
	}

	private function makeEvent(string $type, string $message, string $messageId = '42'): BotInvokeEvent {
		return new BotInvokeEvent(self::BOT_URL, [
			'type' => $type,
			'actor' => ['type' => 'Person', 'id' => 'users/testuser', 'name' => 'Test User'],
			'object' => [
				'type' => 'Note',
				'id' => $messageId,
				'name' => 'message',
				'content' => json_encode(['message' => $message, 'parameters' => []]),
				'mediaType' => 'text/markdown',
			],
			'target' => ['type' => 'Collection', 'id' => 'roomtoken', 'name' => 'Test Room'],
		]);
	}

	public function testIgnoresNonBotInvokeEvent(): void {
		$event = new Event();
		$this->listener->handle($event);
		// No exception = pass; nothing to assert on a plain Event
		$this->addToAssertionCount(1);
	}

	public function testIgnoresWrongBotUrl(): void {
		$event = new BotInvokeEvent('nextcloudapp://some_other_bot', [
			'type' => 'Mention',
			'object' => ['id' => '1', 'content' => json_encode(['message' => 'hello', 'parameters' => []])],
		]);
		$this->listener->handle($event);
		$this->assertEmpty($event->getAnswers());
	}

	public function testSlashCommandReturnsAnswer(): void {
		$event = $this->makeEvent('Create', '/8ball Will this work?', '99');
		$this->listener->handle($event);

		$answers = $event->getAnswers();
		$this->assertCount(1, $answers);
		$this->assertStringStartsWith('🎱 ', $answers[0]['message']);
		$this->assertSame(99, $answers[0]['reply']);
	}

	public function testSlashCommandAloneReturnsAnswer(): void {
		$event = $this->makeEvent('Create', '/8ball');
		$this->listener->handle($event);

		$this->assertCount(1, $event->getAnswers());
	}

	public function testSlashCommandNotMatchedReturnsNoAnswer(): void {
		$event = $this->makeEvent('Create', '/roll 2d6');
		$this->listener->handle($event);

		$this->assertEmpty($event->getAnswers());
	}

	public function testMentionReturnsAnswer(): void {
		$event = $this->makeEvent('Mention', 'Will it rain tomorrow? {mention-bot1}', '7');
		$this->listener->handle($event);

		$answers = $event->getAnswers();
		$this->assertCount(1, $answers);
		$this->assertStringStartsWith('🎱 ', $answers[0]['message']);
		$this->assertSame(7, $answers[0]['reply']);
	}

	public function testJoinEventReturnsNoAnswer(): void {
		$event = $this->makeEvent('Join', '');
		$this->listener->handle($event);

		$this->assertEmpty($event->getAnswers());
	}

	public function testAnswerIsAlwaysFromValidSet(): void {
		$validAnswers = [
			'🟢 It is certain.',
			'🟢 It is decidedly so.',
			'🟢 Without a doubt.',
			'🟢 Yes, definitely.',
			'🟢 You may rely on it.',
			'🟢 As I see it, yes.',
			'🟢 Most likely.',
			'🟢 Outlook good.',
			'🟢 Yes.',
			'🟢 Signs point to yes.',
			'🟡 Reply hazy, try again.',
			'🟡 Ask again later.',
			'🟡 Better not tell you now.',
			'🟡 Cannot predict now.',
			'🟡 Concentrate and ask again.',
			'🔴 Don\'t count on it.',
			'🔴 My reply is no.',
			'🔴 My sources say no.',
			'🔴 Outlook not so good.',
			'🔴 Very doubtful.',
		];
		$fullAnswers = array_map(static fn (string $a): string => '🎱 ' . $a, $validAnswers);

		for ($i = 0; $i < 30; $i++) {
			$event = $this->makeEvent('Mention', 'question');
			$this->listener->handle($event);
			$answer = $event->getAnswers()[0]['message'];
			$this->assertContains($answer, $fullAnswers, "Unexpected answer: $answer");
		}
	}
}
