<?php

namespace skyblock\items\rarity;

use pocketmine\utils\TextFormat;

final class Rarity{

	public static function admin() : self{
		return new self("admin", "ADMIN", TextFormat::DARK_RED, 7);
	}

	public static function legendary() : self{
		return new self("legendary", "LEGENDARY", TextFormat::GOLD, 5);
	}

	public static function special() : self{
		return new self("special", "SPECIAL", TextFormat::RED, 6);
	}

	public static function epic() : self{
		return new self("epic", "EPIC", TextFormat::DARK_PURPLE, 4);
	}

	public static function rare() : self{
		return new self("rare", "RARE", TextFormat::DARK_AQUA, 3);
	}

	public static function common() : self{
		return new self("common", "COMMON", TextFormat::WHITE, 1);
	}

	public static function uncommon() : self{
		return new self("uncommon", "UNCOMMON", TextFormat::GREEN, 2);
	}

	private string $id;
	private string $displayName;
	private string $color;
	private int $tier;

	public function __construct(string $id, string $displayName, string $color, int
	$tier){
		$this->id = $id;
		$this->displayName = $displayName;
		$this->color = $color;
		$this->tier = $tier;
	}

	public function equals(self $rarity) : bool{
		return $this->id === $rarity->getId();
	}

	public function getId() : string{
		return $this->id;
	}

	public function getDisplayName() : string{
		return $this->getColor() . $this->displayName;
	}

	public function getColor() : string{
		return $this->color;
	}

	public function getTier() : int{
		return $this->tier;
	}
}