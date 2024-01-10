<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\item\Item;
use skyblock\player\PvePlayer;

class TeleportAbility extends ItemAbility {
	public function __construct(
		private int $range,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		$data = $player->getLineOfSight($this->range);

		$found = false;
		foreach ($data as $key => $block) {
			if ($block instanceof Air) {
				continue;
			}

			if (!$block instanceof Air) {
				$found = $data[$key - 1] ?? null;
			}
		}

		if ($found === false) {
			$player->teleport(end($data)->getPosition());
			return true;
		}

		if ($found === null) {
			return false;
		}

		if ($found instanceof Block) {
			$player->teleport($found->getPosition());
			return true;
		}

		return false;
	}
}
