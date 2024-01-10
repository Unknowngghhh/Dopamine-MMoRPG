<?php

declare(strict_types=1);

namespace skyblock\items;

use customiesdevs\customies\item\component\ArmorComponent;
use customiesdevs\customies\item\component\IconComponent;
use customiesdevs\customies\item\component\RenderOffsetsComponent;
use customiesdevs\customies\item\component\WearableComponent;
use customiesdevs\customies\item\ItemComponents;
use pocketmine\block\Block;
use pocketmine\color\Color;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\player\Player;
use pocketmine\utils\Binary;
use skyblock\items\armor\ArmorSet;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;

class SkyblockArmor extends Equipment {
	public const TAG_CUSTOM_COLOR = 'customColor';

	private SkyBlockArmorInfo $armorInfo;

	/** @var Color|null */
	protected $customColor = null;

	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown',
		SkyBlockArmorInfo $armorInfo = null
	) {
		parent::__construct($identifier, $name);

		$this->setProperties(
			$this->getProperties()
				->setType(SkyblockItemProperties::ITEM_TYPE_ARMOR)
				->setRarity($armorInfo->getRarity())
		);

		$this->armorInfo = $armorInfo;

		$this->properties->setUnique(true);

		if ($this instanceof ItemComponents) {
			$slot = match ($this->getArmorSlot()) {
				ArmorInventory::SLOT_HEAD => WearableComponent::SLOT_ARMOR_HEAD,
				ArmorInventory::SLOT_CHEST
					=> WearableComponent::SLOT_ARMOR_CHEST,
				ArmorInventory::SLOT_LEGS => WearableComponent::SLOT_ARMOR_LEGS,
				ArmorInventory::SLOT_FEET => WearableComponent::SLOT_ARMOR_FEET,
				default => WearableComponent::SLOT_ARMOR
			};

			$this->addComponent(new ArmorComponent($this->getDefensePoints()));
			$this->addComponent(new WearableComponent($slot));
		}

		$piece = match ($armorInfo->getArmorSlot()) {
			ArmorInventory::SLOT_CHEST => ArmorSet::PIECE_CHESTPLATE,
			ArmorInventory::SLOT_LEGS => ArmorSet::PIECE_LEGGINGS,
			ArmorInventory::SLOT_FEET => ArmorSet::PIECE_BOOTS,
			ArmorInventory::SLOT_HEAD => ArmorSet::PIECE_HELMET
		};

		if ($this->getArmorSet()) {
			$set = $this->getArmorSet();

			$this->properties->setRarity($set->getRarity());

			foreach ($set->getItemAttributes($piece) as $itemAttribute) {
				$this->setItemAttribute($itemAttribute);
			}

			$this->properties->setDescription($set->getLore($this));
			$this->setCustomName($set->getName($piece));

			$this->resetLore();

			$this->getNamedTag()->setString(
				'armor_set',
				$this->getArmorSet()->getIdentifier()
			);
		} else {
			$this->setItemAttribute(
				new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					$armorInfo->getDefensePoints()
				)
			);
		}
	}

	public function getCustomColor(): ?Color {
		return $this->customColor;
	}

	public function setCustomColor(Color $color): self {
		$this->customColor = $color;
		return $this;
	}

	public function clearCustomColor(): self {
		$this->customColor = null;
		return $this;
	}

	public function buildProperties(): SkyblockItemProperties {
		return new SkyblockItemProperties();
	}

	public function resetLore(array $lore = []): void {
		parent::resetLore($lore);
	}

	public function getArmorSlot(): int {
		return $this->armorInfo->getArmorSlot();
	}

	public function getMaxStackSize(): int {
		return 1;
	}

	public function getArmorSet(): ?ArmorSet {
		return null;
	}

	public function onClickAir(
		Player $player,
		Vector3 $directionVector,
		array &$returnedItems
	): ItemUseResult {
		$existing = $player
			->getArmorInventory()
			->getItem($this->getArmorSlot());
		$thisCopy = clone $this;
		$new = $thisCopy->pop();
		if ($new instanceof SkyblockItem) {
			if ($new->getProperties()->isUnique()) {
				$new->makeUnique();
			}
		}

		$player->getArmorInventory()->setItem($this->getArmorSlot(), $new);
		if ($thisCopy->getCount() === 0) {
			$player->getInventory()->setItemInHand($existing);
		} else {
			//if the stack size was bigger than 1 (usually won't happen, but might be caused by plugins
			$player->getInventory()->setItemInHand($thisCopy);
			$player->getInventory()->addItem($existing);
		}

		return ItemUseResult::SUCCESS();
	}

	protected function deserializeCompoundTag(CompoundTag $tag): void {
		parent::deserializeCompoundTag($tag);
		if (
			($colorTag = $tag->getTag(self::TAG_CUSTOM_COLOR)) instanceof IntTag
		) {
			$this->customColor = Color::fromARGB(
				Binary::unsignInt($colorTag->getValue())
			);
		} else {
			$this->customColor = null;
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);
		$this->customColor !== null
			? $tag->setInt(
				self::TAG_CUSTOM_COLOR,
				Binary::signInt($this->customColor->toARGB())
			)
			: $tag->removeTag(self::TAG_CUSTOM_COLOR);
	}
}
