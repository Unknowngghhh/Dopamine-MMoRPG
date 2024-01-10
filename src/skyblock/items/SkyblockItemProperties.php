<?php

declare(strict_types=1);

namespace skyblock\items;

use skyblock\items\rarity\Rarity;

class SkyblockItemProperties {
	const ITEM_TYPE_REGULAR = '';
	const ITEM_TYPE_ARMOR = 'ARMOR';
	const ITEM_TYPE_WEAPON = 'WEAPON';
	const ITEM_TYPE_TOOL = 'TOOL';
	const ITEM_TYPE_BOW = 'BOW';
	const ITEM_TYPE_FISHING_ROD = 'FISHING ROD';
	const ITEM_TYPE_ACCESSORY = 'ACCESSORY';
	const ITEM_TYPE_BOOK = 'BOOK';

	protected int $maxUses = 0;

	protected bool $canAuction = true;

	protected bool $canTrade = true;

	protected bool $unique = false;

	protected Rarity $rarity;

	protected array $description = [];

	protected string $type = '';

	public function __construct() {
		$this->setRarity(Rarity::common());
	}

	/**
	 * @return int If the item can be
	 * "used", this determines the
	 * maximum uses of the item.
	 */
	public function getMaxUses(): int {
		return $this->maxUses;
	}

	public function setMaxUses(int $uses): self {
		if ($uses < 0) {
			throw new \InvalidArgumentException(
				'Max Uses must be a positive integer'
			);
		}

		$this->maxUses = $uses;
		return $this;
	}

	/**
	 * @return bool If this is set to
	 * false, the item will not be
	 * able to be traded with other
	 * players.
	 */
	public function canTrade(): bool {
		return $this->canTrade;
	}

	public function setCanTrade(bool $canTrade): self {
		$this->canTrade = $canTrade;

		return $this;
	}

	/**
	 * @return bool If this is set to
	 * false, players will not be
	 * able to put this item on the
	 * auction house.
	 */
	public function canAuction(): bool {
		return $this->canAuction;
	}

	public function setCanAuction(bool $canAuction): self {
		$this->canAuction = $canAuction;

		return $this;
	}

	/**
	 * @return bool If this is set to
	 * true the item will be considered
	 * unique and have an identifier. This
	 * can be used to detect for duplicated
	 * items.
	 */
	public function isUnique(): bool {
		return $this->unique;
	}

	public function setUnique(bool $unique): self {
		$this->unique = $unique;

		return $this;
	}

	/**
	 * @return Rarity The default rarity
	 * of this item, this can be changed
	 * by the item.
	 */
	public function getRarity(): Rarity {
		return $this->rarity;
	}

	public function setRarity(Rarity $rarity): self {
		$this->rarity = $rarity;

		return $this;
	}

	/**
	 * @return array The default description
	 * of the item. This is used to re-add the
	 * description when an items lore is
	 * updated.
	 */
	public function getDescription(): array {
		return $this->description;
	}

	public function setDescription(array $description): self {
		$this->description = $description;

		return $this;
	}

	/**
	 * @return string the type of the item that will be shown
	 * in the lore. e.g. COMMON SWORD if empty this will be just e.g. COMMON
	 */
	public function getType(): string {
		return $this->type;
	}

	public function setType(string $type): self {
		$this->type = $type;

		return $this;
	}
}
