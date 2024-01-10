<?php

declare(strict_types=1);

namespace skyblock\items\itemattribute;

use skyblock\traits\HandlerTrait;
use skyblock\utils\PveUtils;
use function mb_chr;

class SkyBlockItemAttributeFactory {
	use HandlerTrait;

	private array $list = [];

	public function onEnable(): void {
		$this->register(
			new ItemAttribute('Strength', mb_chr(0xe1f3), '§c')
		);
		$this->register(
			new ItemAttribute('Intelligence', mb_chr(0xe1f5), '§b')
		);
		$this->register(
			new ItemAttribute('Critical Chance', mb_chr(0xe1eb), '§6', true)
		);
		$this->register(
			new ItemAttribute('Critical Damage', mb_chr(0xe1eb), '§6')
		);
		$this->register(new ItemAttribute('Defense', mb_chr(0xe1e9), '§a'));
		$this->register(new ItemAttribute('Damage', mb_chr(0xe100), '§c'));
		$this->register(new ItemAttribute('Speed', mb_chr(0xe1f4), '§f'));
		$this->register(new ItemAttribute('Health', mb_chr(0xe1e8), '§c'));
		$this->register(new ItemAttribute('Mining Speed', '', '§g'));
		$this->register(new ItemAttribute('Mining Wisdom', '', '§3'));
		$this->register(new ItemAttribute('Foraging Wisdom', '', '§3'));
		$this->register(new ItemAttribute('Combat Wisdom', '', '§3'));
		$this->register(new ItemAttribute('Mining Fortune', '', '§6'));
		$this->register(new ItemAttribute('Foraging Fortune', '', '§6'));
		$this->register(new ItemAttribute('Sea Creature Chance', '', '§6'));
		$this->register(new ItemAttribute('Fishing Speed', '', '§6'));
	}

	public function get(string $name): ?ItemAttribute {
		return $this->list[str_replace(' ', '_', strtolower($name))] ?? null;
	}

	public function register(ItemAttribute $item): void {
		$this->list[
			str_replace(' ', '_', strtolower($item->getName()))
		] = $item;
	}
}
