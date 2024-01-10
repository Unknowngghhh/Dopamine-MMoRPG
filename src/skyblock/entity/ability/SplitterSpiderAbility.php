<?php

declare(strict_types=1);

namespace skyblock\entity\ability;

use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\PveHandler;

class SplitterSpiderAbility extends MobAbility {
	public function attack(
		Player $player,
		PveEntity $entity,
		float $baseDamage
	): bool {
		return true;
	}

	public static function getId(): string {
		return 'splitter-spider-split';
	}

	public function onTick(PveEntity $entity, int $tick): void {
	}

	public function onDeath(PveEntity $entity, EntityDeathEvent $event): void {
		$loc = $entity->getLocation();

		for ($i = 0; $i <= 1; $i++) {
			$d = PveHandler::getInstance()->getEntities()[
				'splitter-silverfish'
			];
			$e = new PveEntity(
				$d['networkID'],
				Location::fromObject($loc, $loc->getWorld()),
				$d['nbt']
			);
			$e->spawnToAll();
		}
	}

	public function onDamage(
		PveEntity $entity,
		PlayerAttackEntityEvent $event
	): void {
	}
}
