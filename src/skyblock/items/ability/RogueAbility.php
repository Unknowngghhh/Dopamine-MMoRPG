<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\item\Item;
use skyblock\player\PvePlayer;
use skyblock\traits\AwaitStdTrait;
use SOFe\AwaitGenerator\Await;

class RogueAbility extends ItemAbility {
	use AwaitStdTrait;

	public function __construct(
		private int $speedboost,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		$player
			->getPveData()
			->setSpeed($player->getPveData()->getSpeed() + 100);

		Await::f2c(function () use ($player) {
			yield $this->getStd()->sleep(20 * 30);

			if (!$player->isOnline()) {
				return;
			}
			$player
				->getPveData()
				->setSpeed($player->getPveData()->getSpeed() - 100);
		});

		return true;
	}
}
