<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Magic8BallBot\Migration;

use OCA\Magic8BallBot\AppInfo\Application;
use OCA\Talk\Events\BotInstallEvent;
use OCA\Talk\Model\Bot;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\Security\ISecureRandom;

/**
 * @psalm-api
 */
class InstallBot implements IRepairStep {
	public function __construct(
		protected IEventDispatcher $dispatcher,
		protected ISecureRandom $secureRandom,
		protected IAppConfig $appConfig,
	) {
	}

	#[\Override]
	public function getName(): string {
		return 'Install Magic 8-Ball as Talk bot';
	}

	#[\Override]
	public function run(IOutput $output): void {
		if (!class_exists(BotInstallEvent::class)) {
			$output->warning('Talk not found, not installing bot');
			return;
		}

		$secret = $this->appConfig->getAppValueString('secret');
		if ($secret === '') {
			$secret = $this->secureRandom->generate(128);
			$this->appConfig->setAppValueString('secret', $secret, sensitive: true);
		}

		$event = new BotInstallEvent(
			'Magic 8-Ball',
			$secret,
			'nextcloudapp://' . Application::APP_ID,
			'Ask the Magic 8-Ball a yes/no question using `/8ball` followed by your question.',
			Bot::FEATURE_EVENT | Bot::FEATURE_MENTION,
		);
		$this->dispatcher->dispatchTyped($event);
	}
}
