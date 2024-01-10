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
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\world\ChunkLoadEvent;
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
use skyblock\player\CustomPlayerArmorInventoryListener;
use skyblock\player\CustomPlayerInventoryListener;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\events\EntityAttackPlayerEvent;
use skyblock\events\PlayerKillEntityEvent;
use skyblock\events\EntityKillPlayerEvent;
use skyblock\traits\AwaitStdTrait;
use skyblock\items\SkyblockItem;
use skyblock\utils\EntityUtils;
use skyblock\entity\PveEntity;
use skyblock\tasks\TipUpdater;
use skyblock\utils\Utils;
use skyblock\player\PvePlayer;
use skyblock\PveHandler;
use skyblock\Main;
use SOFe\AwaitGenerator\Await;
use Closure;

class PveListener implements Listener {
	use AwaitStdTrait;

	public function onPlayerCreation(PlayerCreationEvent $event): void {
		$event->setPlayerClass(PvePlayer::class);
	}

	public function onJoin(PlayerJoinEvent $event): void {
		/** @var PvePlayer $player */
		$player = $event->getPlayer();
		$player->getInventory()->getListeners()->add(($listener = new CustomPlayerInventoryListener($player)));
		$player->getInventory()->getHeldItemIndexChangeListeners()->add(Closure::fromCallable([$listener, "onHeldItemIndexChange"]));
		$player->getArmorInventory()->getListeners()->add($list = new CustomPlayerArmorInventoryListener($player));
		Utils::executeLater(function() use($player, $list) {
			if($player->isOnline()){
				$list->onSlotChange($player->getArmorInventory(), ArmorInventory::SLOT_HEAD, VanillaItems::AIR());
				$list->onSlotChange($player->getArmorInventory(), ArmorInventory::SLOT_CHEST, VanillaItems::AIR());
				$list->onSlotChange($player->getArmorInventory(), ArmorInventory::SLOT_LEGS, VanillaItems::AIR());
				$list->onSlotChange($player->getArmorInventory(), ArmorInventory::SLOT_FEET, VanillaItems::AIR());
			}
		}, 10);
	}

	public function onPlayerInteract(PlayerInteractEvent $event): void {
		$player = $event->getPlayer();
		$action = $event->getAction();
		$item = $event->getItem();

		if (
			$player->getWorld()->getId() ===
			PveHandler::getInstance()
				->getPveWorld()
				->getId()
		) {
			if (
				$item instanceof Hoe &&
				$action === PlayerInteractEvent::RIGHT_CLICK_BLOCK
			) {
				if ($player->isSurvival()) {
					$event->cancel();
				}
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGHEST
	 * @handleCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event): void {
		$block = $event->getBlock();
		/** @var PvePlayer $player */
		$player = $event->getPlayer();

		if (
			$player->getWorld()->getId() !==
			PveHandler::getInstance()
				->getPveWorld()
				->getId()
		) {
			return;
		}
		if ($player->isCreative()) {
			return;
		}

		if (Utils::isOre($block)) {
			$drops = $block->getDrops($player->getInventory()->getItemInHand());
			$miningFortune = $player->getPveData()->getMiningFortune();
			$add = (int) floor($miningFortune / 100);
			$rest = $miningFortune % 100;
			foreach ($drops as $drop) {
				if ($miningFortune > 100) {
					$drop->setCount($drop->getCount() + $add);
				}

				if (mt_rand(1, 100) <= $rest) {
					$drop->setCount($drop->getCount() + 1);
				}
			}
			$event->setDrops($drops);

			Utils::executeLater(function () use ($block, $player) {
				$player
					->getWorld()
					->setBlock($block->getPosition(), VanillaBlocks::BEDROCK());
			}, 1);
			$event->uncancel();
		}

		if (Utils::isWood($block)) {
			$event->uncancel();

			$world = $player->getWorld();
			$block = $event->getBlock();
			$pos = $block->getPosition();

			$foragingFortune = $player->getPveData()->getForagingFortune();
			$drops = $block->getDrops($player->getInventory()->getItemInHand());

			$add = (int) floor($foragingFortune / 100);
			$rest = $foragingFortune % 100;
			foreach ($drops as $drop) {
				if ($foragingFortune > 100) {
					$drop->setCount($drop->getCount() + $add);
				}

				if (mt_rand(1, 100) <= $rest) {
					$drop->setCount($drop->getCount() + 1);
				}
			}

			$event->setDrops($drops);
			$world->setBlock($block->getPosition(), VanillaBlocks::AIR());
			Utils::executeLater(
				function () use ($pos, $block, $world) {
					$world->setBlock($pos, $block);
				},
				60 * mt_rand(5, 10),
				true
			);
		}
		$item = $event->getItem();
		if ($item instanceof SkyblockItem) {
			$item->onCustomDestroyBlock($player, $event);
		}
	}

	/**
	 * @param EntityAttackPlayerEvent $event
	 * @priority  NORMAL
	 */
	public function onEntityAttackPlayer(EntityAttackPlayerEvent $event): void {
		$player = $event->getPlayer();
		$entity = $event->getEntity();
		$damage = $event->getFinalDamage();

		$defense = $player->getPveData()->getDefense();

		$reduceDamage = $defense / ($defense + 100);
		$newDamage = max(0, $damage * (1 - $reduceDamage));

		$health = $player->getPveData()->getHealth();
		if ($health <= $newDamage) {
			$player->teleport($player->getWorld()->getSpawnLocation());
			$player->sendMessage(
				'☠️§7You died while fighting §c' . $entity->getName() . '.'
			);

			$player
				->getPveData()
				->setHealth($player->getPveData()->getMaxHealth());

			(new PveKillPlayerEvent($player, $entity, $event))->call();
			return;
		}

		$health -= $newDamage;
		$player->getPveData()->setHealth($health);

		$deltaX = $player->getPosition()->x - $entity->getPosition()->x;
		$deltaZ = $player->getPosition()->z - $entity->getPosition()->z;
		$player->knockBack($deltaX, $deltaZ, $event->getKnockback());

		$player->doHitAnimationCustom();
	}

	/**
	 * @param PlayerAttackEntityEvent $event
	 * @priority MONITOR
	 *
	 * @return void
	 */
	public function onPlayerAttackPvE(PlayerAttackEntityEvent $event): void {
		$player = $event->getPlayer();
		$entity = $event->getEntity();

		//$event->setDamage(mt_rand(80, 150));

		foreach ($entity->abilities as $id) {
			$ability = PveHandler::getInstance()->getAbility($id);

			if ($ability === null) {
				continue;
			}

			$ability->onDamage($entity, $event);
		}

		$item = $player->getInventory()->getItemInHand();

		if ($item instanceof SkyblockItem) {
			$item->onAttackPve($player, $event);
		}

		$finalDamage = $event->getFinalDamage();

		$string = number_format($finalDamage, 0);

		if ($event->isCritical()) {
			$string = "✧{$finalDamage}✧";
		}

		$sending = '';
		foreach (str_split($string) as $v) {
			$sending .= Utils::getRandomColor() . $v;
		}

		$entity->setLastDamageSource($event);
		Utils::spawnTextEntity($entity->getLocation(), $sending, 1, [$player]);
		$entity->setHealth($entity->getHealth() - $finalDamage);
		if ($entity->hostile) {
			if ($entity->getTarget() === null) {
				$entity->setTarget($player);
			}
		}

		$deltaX = $entity->getLocation()->x - $player->getLocation()->x;
		$deltaZ = $entity->getLocation()->z - $player->getLocation()->z;
		$entity->knockBack($deltaX, $deltaZ, $event->getKnockback());
		$entity->broadcastAnimation(new HurtAnimation($entity));
	}

	/**
	 * @param EntityItemPickupEvent $event
	 * @priority LOW
	 * @return void
	 */
	public function onItemPickup(EntityItemPickupEvent $event): void {
		$p = $event->getEntity();

		if (!$p instanceof PvePlayer) {
			return;
		}

		$itemEntity = $event->getOrigin();

		if (!$itemEntity instanceof ItemEntity) {
			return;
		}

		$i = $itemEntity->getItem();
		if ($i->getNamedTag()->getByte('collection', -1) !== -1) {
			$itemEntity->flagForDespawn();
			$event->cancel();

			$i->getNamedTag()->removeTag('collection');
			Utils::addItem($p, $i, false, true);
		}
	}

	public function onPlayerKillEntity(PlayerKillEntityEvent $event): void {
		$player = $event->getPlayer();
		$entity = $event->getEntity();

		$player
			->getNetworkSession()
			->sendDataPacket(
				Utils::getSoundPacket('random.orb', $player->getLocation())
			);

		if ($entity->coins > 0) {
			$player->sendMessage('§g+' . $entity->coins . ' §ecoins (BETA)');
		}
	}

	/**
	 * @param EntityDespawnEvent $event
	 * @priority NORMAL
	 */
	public function onEntityDespawn(EntityDespawnEvent $event): void {
		$entity = $event->getEntity();

		if ($entity instanceof PveEntity) {
			/*if (($zone = $entity->getZone()) !== null) {
				$zone->decreaseMob($entity->getZoneMobName());
			}*/
		}
	}

	public function onInventoryTransaction(
		InventoryTransactionEvent $event
	): void {
		$trans = $event->getTransaction();
		$p = $trans->getSource();

		foreach ($trans->getInventories() as $inventory) {
			if (!$inventory instanceof PlayerInventory) {
				continue;
			}

			foreach ($trans->getActions() as $action) {
				if ($action instanceof SlotChangeAction) {
					if ($action->getSlot() === 8) {
						$event->cancel();
						return;
					}
				}

				if ($action instanceof DropItemAction) {
					if (
						$p
							->getInventory()
							->getHotbarSlotItem(8)
							->equals($action->getTargetItem())
					) {
						$event->cancel();
						return;
					}
				}
			}
		}
	}
}
