<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\item\Item;
use pocketmine\entity\Location;
use skyblock\entity\projectile\SkyBlockProjectile;
use skyblock\entity\projectile\InkBombEntity;
use skyblock\entity\projectile\DreadlordSkullEntity;
use skyblock\player\PvePlayer;
use skyblock\traits\AwaitStdTrait;

class ShootProjectileAbility extends ItemAbility {
	use AwaitStdTrait;

	public function __construct(
		private string $projectileClass,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		$location = $player->getLocation();
		$entity = new $this->projectileClass(
			Location::fromObject(
				$player->getEyePos(),
				$player->getWorld(),
				($location->yaw > 180 ? 360 : 0) - $location->yaw,
				-$location->pitch
			),
			$player
		);
		$entity->setMotion($player->getDirectionVector());

		if ($entity instanceof SkyBlockProjectile) {
			$entity->setSourceItem($item);
		}

		$entity->spawnToAll();
		return true;
	}
}
