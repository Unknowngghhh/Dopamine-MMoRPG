<?php

declare(strict_types=1);

namespace skyblock\entity\ability;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\Main;
use skyblock\PveHandler;
use skyblock\traits\AwaitStdTrait;
use skyblock\utils\Utils;
use SOFe\AwaitGenerator\Await;

class ArachneBossAbility extends MobAbility{
	use AwaitStdTrait;

	private array $data = [];

	public function attack(Player $player, PveEntity $entity, float $baseDamage) : bool {
		return true;
	}

	public static function getId() : string{
		return "arachne-boss-ability";
	}

	public function onTick(PveEntity $entity, int $tick) : void{

		if($tick % 10 && mt_rand(1, 100) === 1){
			$e = $entity->getTarget();

			if($e === null) return;

			$diff = $e->getHealth() - $e->getMaxHealth() * 0.01;
			$e->setHealth($e->getMaxHealth() * 0.01);
			$e->sendMessage("§dArachne §2used§d Venom Shot on you hitting you for §a" . number_format($diff) . " damage§d and infecting you with venom.");
			$e->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * 5, 10));
		}
	}

	public function onDeath(PveEntity $entity, EntityDeathEvent $event) : void{
		//AreaHandler::SPIDERS_DEN()->message("§c[BOSS] Arachne:§f No, this is impossible... I will be back, even stronger!");

		$data = $this->data[$entity->getId()]["damage"];
		uasort($data, function($a, $b) {
			return $b - $a;
		});

		$message = [
			"§b" . str_repeat("-", 20),
			"§r§6§lARACHNE DOWN!",
			"§r",
		];

		for($i = 0; $i <= 2; $i++){
			$keys = array_keys($data);

			if(isset($keys[$i])){
				$order = $i+1;
				$message[] = "§e§l$order" . ($i === 0 ? "st" : "nd") . " Damager§r§7 - §b" . $keys[$i] . "§7 - §e ". number_format($data[$keys[$i]]);


				if($entity->loottable){
					$p = Server::getInstance()->getPlayerExact($keys[$i]);

					if($p === null) continue;
					/** @var WeightedItem $item */
					foreach($entity->loottable->generate($entity->totaldrops) as $item){
						Utils::addItem($p, $item->getItem()->setCount(mt_rand($item->getMinCount(), $item->getMaxCount())));
					}
				}
			}
		}

		$message[] = "§r";
		$message[] = "§b" . str_repeat("-", 20);

		Await::f2c(function() use ($entity){
			yield $this->getStd()->sleep(20 * 5);
			unset($this->data[$entity->getId()]);
		});
	}

	public function onDamage(PveEntity $entity, PlayerAttackEntityEvent $event) : void{
		$p = $event->getPlayer();
		$this->data[$entity->getId()]["damage"][$p->getName()] =  ($this->data[$entity->getId()]["damage"][$p->getName()] ?? 0) + $event->getFinalDamage();

		$twentyPercent = $entity->getMaxHealth() * 0.20;
		$fiftyPercent = $entity->getMaxHealth() * 0.50;

		if($entity->getHealth() <= $fiftyPercent && !isset($this->data[$entity->getId()]["first"])){
			$this->data[$entity->getId()]["first"] = true;

			//AreaHandler::SPIDERS_DEN()->message("§c[BOSS] §fArachne: GaHahahahAAHAa, you are so annoying, you're not the only one with a trick up their sleeves!");
			Await::f2c(function() use($entity) {
				for($i = 1; $i <= 10; $i++){
					$d = PveHandler::getInstance()->getEntities()["arachnes-brood"];
					$e = new PveEntity($d["networkID"], Location::fromObject($entity->getLocation(), $entity->getWorld()), $d["nbt"]);
					$e->spawnToAll();

					yield $this->getStd()->sleep(1);
				}
			});
		}

		if($entity->getHealth() <= $twentyPercent && !isset($this->data[$entity->getId()]["second"])){
			$this->data[$entity->getId()]["second"] = true;

			//AreaHandler::SPIDERS_DEN()->message("§c[BOSS] Arachne: §fThat's enough of this if 10 isn't enough, I will swarm you with 20!");

			Await::f2c(function() use($entity) {
				for($i = 1; $i <= 20; $i++){
					$d = PveHandler::getInstance()->getEntities()["arachnes-brood"];
					$e = new PveEntity($d["networkID"], Location::fromObject($entity->getLocation(), $entity->getWorld()), $d["nbt"]);
					$e->spawnToAll();

					yield $this->getStd()->sleep(1);
				}
			});
		}
	}
}