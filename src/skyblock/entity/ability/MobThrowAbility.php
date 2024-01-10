<?php


declare(strict_types=1);

namespace skyblock\entity\ability;

use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackEntityEvent;

class MobThrowAbility extends MobAbility{

	public function attack(Player $player, PveEntity $entity, float $baseDamage) : bool {
		$deltaX = $player->getPosition()->x - $entity->getPosition()->x;
		$deltaZ = $player->getPosition()->z - $entity->getPosition()->z;
		$player->knockBack($deltaX*2, $deltaZ*2, 2.8, 0.8);

		Server::getInstance()->broadcastPackets($entity->getViewers(), [ActorEventPacket::create($entity->getId(), ActorEvent::ARM_SWING, 0)]);

		return true;
	}

	public static function getId() : string{
		return "throw";
	}

	public function onTick(PveEntity $entity, int $tick) : void{}

	public function onDeath(PveEntity $entity, EntityDeathEvent $event) : void{}

	public function onDamage(PveEntity $entity, PlayerAttackEntityEvent $event) : void{}
}