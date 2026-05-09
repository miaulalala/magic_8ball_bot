# Magic 8-Ball for Nextcloud Talk

A Nextcloud app that adds a Magic 8-Ball bot to Talk conversations. Ask it a yes/no question and receive a mystical answer.

## Usage

Once installed and added to a conversation, there are two ways to consult the Magic 8-Ball:

**@mention** (requires Talk with `FEATURE_MENTION` support):
> @Magic 8-Ball Will this PR get merged?

**Slash command:**
> /8ball Will this PR get merged?

The bot replies inline with one of the 20 classic Magic 8-Ball answers, colour-coded by category:
- 🟢 Positive (10 answers)
- 🟡 Neutral (5 answers)
- 🔴 Negative (5 answers)

## Installation

1. Clone or copy this app into your Nextcloud `apps-extra/` (or `apps/`) directory.
2. Enable it: `php occ app:enable magic_8ball_bot`
3. The bot registers itself automatically on install.
4. Add it to a conversation: `php occ talk:bot:setup <botId> <roomToken>`

Find the bot ID with `php occ talk:bot:list`.

## Requirements

- Nextcloud 34
- Nextcloud Talk 24

## License

AGPL-3.0-or-later
