<?php

declare(strict_types=1);

namespace skyblock\items\crafting;

use Closure;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyblockItem;
use skyblock\items\SkyblockItemProperties;
use skyblock\traits\AwaitStdTrait;
use SOFe\AwaitGenerator\Await;

class SkyBlockEnchantedItem extends SkyblockItem {
	use AwaitStdTrait;

	private Item $recipeItem;

	private ?Item $middleItem = null;

	public function __construct(Item $item, string $name = 'Unknown') {
		parent::__construct($identifier, $name);

		$this->makeGlow();

		$this->setCustomName(
			'§r' . $this->properties->getRarity()->getColor() . $name
		);
		$this->recipeItem = $item;
	}

	//closure that returns item
	public function setRecipeItem(Closure $item): self {
		Await::f2c(function () use ($item) {
			yield $this->getStd()->sleep(1);
			$this->recipeItem = $item();
		});
		return $this;
	}

	public function setMiddleItem(Closure $item): self {
		Await::f2c(function () use ($item) {
			yield $this->getStd()->sleep(1);
			$this->middleItem = $item();
		});
		return $this;
	}

	public function getRecipeItem(): Item {
		return clone $this->recipeItem;
	}

	public function getMiddleItem(): ?Item {
		return $this->middleItem;
	}

	public function buildProperties(): SkyblockItemProperties {
		return (new SkyblockItemProperties())->setRarity(Rarity::uncommon());
	}
}
