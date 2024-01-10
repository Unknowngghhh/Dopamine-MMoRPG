<?php

declare(strict_types=1);

namespace skyblock\items;

use customiesdevs\customies\item\component\CreativeCategoryComponent;
use customiesdevs\customies\item\component\CreativeGroupComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\events\EntityAttackPlayerEvent;
use skyblock\player\PvePlayer;
use skyblock\traits\AwaitStdTrait;
use SOFe\AwaitGenerator\Await;

abstract class SkyblockItem extends Item {
	use AwaitStdTrait;

	public const NBT_PREFIX = 'skyblock_item:';

	protected SkyblockItemProperties $properties;

	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct($identifier, $name);
		if ($name !== 'unknown') {
			$this->setCustomName($name);
		}
		$this->setProperties($this->buildProperties());

		if ($this->properties->isUnique()) {
			$this->makeUnique();
		}

		$this->resetLore();

		$this->name = '§r' . $this->properties->getRarity()->getColor() . $name;
		$this->setCustomName($this->name);
	}

	public function onTransaction(
		Player $player,
		Item $itemClicked,
		Item $itemClickedWith,
		SlotChangeAction $itemClickedAction,
		SlotChangeAction $itemClickedWithAction,
		InventoryTransactionEvent $event
	): void {
	}

	abstract public function buildProperties(): SkyblockItemProperties;

	public function getProperties(): SkyblockItemProperties {
		return $this->properties;
	}

	public function setProperties(SkyblockItemProperties $properties): void {
		$this->properties = $properties;
		$this->resetLore();
	}

	public function getUniqueId(): string {
		return $this->getNamedTag()->getString('skyblock_unique_id', '');
	}

	public function makeUnique(): void {
		$this->getNamedTag()->setString(
			'skyblock_unique_id',
			uniqid(self::NBT_PREFIX . mt_rand(1, 10000))
		);
	}

	public function isGlowing(): bool {
		return $this->hasEnchantment(
			EnchantmentIdMap::getInstance()->fromId(138)
		);
	}

	public function makeGlow(): void {
		$this->addEnchantment(
			new EnchantmentInstance(
				EnchantmentIdMap::getInstance()->fromId(138)
			)
		);
	}

	public function stopGlow(): void {
		$this->removeEnchantment(EnchantmentIdMap::getInstance()->fromId(138));
	}

	public function getMaxStackSize(): int {
		if ($this->properties->isUnique()) {
			return 1;
		}

		return parent::getMaxStackSize();
	}

	public function onAttackPve(
		PvePlayer $player,
		PlayerAttackEntityEvent $event
	): void {
	}

	public function onCustomDestroyBlock(
		PvePlayer $player,
		BlockBreakEvent $event
	): void {
	}

	public function onProjectileHitEvent(
		PvePlayer $player,
		ProjectileHitEvent $event
	): void {
	}

	public function onProjectileHitEntityEvent(
		PlayerAttackEntityEvent $event
	): void {
	}

	/**
	 * This will reset the item's lore,
	 * essentially updating the display
	 * for any stats that have been updated.
	 */
	public function resetLore(array $lore = []): void {
		$rarity = $this->properties->getRarity();
		$type = $this->properties->getType();
		$string = "§r§l{$rarity->getColor()}{$rarity->getDisplayName()}";

		if ($type !== '') {
			$type = strtoupper($type);
			$string .= " {$type}";
		}

		$desc = $this->properties->getDescription();
		$lore = array_merge(
			empty($lore) ? $lore : array_merge($lore, ['§r']),
			empty($desc) ? $desc : array_merge($desc, ['§r']),
			[$string]
		);

		$this->setLore($lore);
	}
}
