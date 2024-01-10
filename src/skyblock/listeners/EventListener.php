<?php

declare(strict_types=1);

namespace skyblock\listeners;

use pocketmine\block\Bedrock;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Dirt;
use pocketmine\block\Grass;
use pocketmine\block\Leaves;
use pocketmine\block\Slime;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\effect\SpeedEffect;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\item\EnderPearl;
use pocketmine\item\Hoe;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerEnchantOptionsPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\sound\XpLevelUpSound;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\events\EntityAttackPlayerEvent;
use skyblock\events\PlayerKillEntityEvent;
use skyblock\events\EntityKillPlayerEvent;
use skyblock\entity\PveEntity;
use skyblock\player\PvePlayer;
use skyblock\PveHandler;
use skyblock\Main;

class EventListener implements Listener {
	public function onDecay(LeavesDecayEvent $event): void {
		$event->cancel();
	}

	/**
	 * @param ProjectileHitEntityEvent $event
	 * @ignoreCancelled false
	 * @priority HIGH
	 * @return void
	 */
	public function projectileHit(ProjectileHitEntityEvent $event): void {

		$arrow = $event->getEntity();
		$p = $event->getEntityHit();
		$pve = $arrow->getOwningEntity();

		if($p instanceof PvePlayer && $pve instanceof PveEntity){
			(new EntityAttackPlayerEvent($pve, $p, $pve->damage))->call();
		}
	}
}