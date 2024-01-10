<?php

declare(strict_types=1);

namespace skyblock\items\armor\zombie;

use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\items\armor\ArmorSet;
use skyblock\items\armor\ArmorSetAbility;
use skyblock\player\PvePlayer;

class ZombieSetAbility extends ArmorSetAbility {
	public function getDesiredEvents() : array{
		return [ProjectileHitEntityEvent::class];
	}

	public function tryCall(Event $event) : void{
		assert($event instanceof ProjectileHitEntityEvent);
		$p = $event->getEntityHit();

		if($p instanceof PvePlayer){
			if(ArmorSet::getCache($p) instanceof ZombieSet){
				$this->onActivate($p, $event);
			}
		}
	}

	public function onActivate(Player $player, Event $event) : void{
		assert($player instanceof PvePlayer);

		$player->getPveData()->setHealth($player->getPveData()->getHealth() + 10);
	}
}