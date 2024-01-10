<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\block\Block;
use pocketmine\item\Item;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\player\PvePlayer;
use skyblock\traits\AwaitStdTrait;
use SOFe\AwaitGenerator\Await;

class DestroyConnectedBlocksAbility extends ItemAbility {
	use AwaitStdTrait;

	public function __construct(
		private Block $block,
		private int $destroyCount,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		Await::f2c(function () use ($player) {
			$block = $this->block;
			$last = $block;
			$count = 0;
			$arr = [];

			while ($count < $this->destroyCount) {
				$found = false;
				foreach ($last->getAllSides() as $side) {
					if ($side instanceof $block) {
						if (
							in_array(
								(string) $side->getPosition()->asVector3(),
								$arr
							)
						) {
							continue;
						}

						if (!$player->isOnline()) {
							break 2;
						}

						$count++;
						$last = $side;
						$found = true;

						$arr[] = (string) $side->getPosition()->asVector3();
						$block
							->getPosition()
							->getWorld()
							->useBreakOn(
								$side->getPosition(),
								$item,
								$player,
								true
							);
						yield $this->getStd()->sleep(1);
					}
				}

				if (!$found) {
					break;
				}
			}
		});

		return true;
	}
}
