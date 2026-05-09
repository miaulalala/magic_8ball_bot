<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Magic8BallBot\Listener;

use OCA\Magic8BallBot\AppInfo\Application;
use OCA\Talk\Events\BotInvokeEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-implements IEventListener<Event>
 */
class BotInvokeListener implements IEventListener {
	private const ANSWERS = [
		// Positive
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
		// Neutral
		'🟡 Reply hazy, try again.',
		'🟡 Ask again later.',
		'🟡 Better not tell you now.',
		'🟡 Cannot predict now.',
		'🟡 Concentrate and ask again.',
		// Negative
		'🔴 Don\'t count on it.',
		'🔴 My reply is no.',
		'🔴 My sources say no.',
		'🔴 Outlook not so good.',
		'🔴 Very doubtful.',
	];

	public function handle(Event $event): void {
		if (!$event instanceof BotInvokeEvent) {
			return;
		}

		if ($event->getBotUrl() !== 'nextcloudapp://' . Application::APP_ID) {
			return;
		}

		$chatMessage = $event->getMessage();
		$content = json_decode($chatMessage['object']['content'], true);

		if ($chatMessage['type'] === 'Create') {
			if (!str_starts_with($content['message'], '/8ball')) {
				return;
			}
		} elseif ($chatMessage['type'] !== 'Mention') {
			return;
		}

		$answer = self::ANSWERS[array_rand(self::ANSWERS)];
		$event->addAnswer(
			'🎱 ' . $answer,
			(int)$chatMessage['object']['id'],
		);
	}
}
