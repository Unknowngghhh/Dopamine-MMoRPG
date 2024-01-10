<?php

declare(strict_types=1);

namespace skyblock\items\itemattribute;

use pocketmine\utils\RegistryTrait;

/**
 * @method static ItemAttribute STRENGTH()
 * @method static ItemAttribute INTELLIGENCE()
 * @method static ItemAttribute CRITICAL_DAMAGE()
 * @method static ItemAttribute CRITICAL_CHANCE()
 * @method static ItemAttribute FISHING_SPEED()
 * @method static ItemAttribute FORAGING_FORTUNE()
 * @method static ItemAttribute MINING_FORTUNE()
 * @method static ItemAttribute SEA_CREATURE_CHANCE()
 * @method static ItemAttribute COMBAT_WISDOM()
 * @method static ItemAttribute MINING_WISDOM()
 * @method static ItemAttribute FORAGING_WISDOM()
 * @method static ItemAttribute MINING_SPEED()
 * @method static ItemAttribute HEALTH()
 * @method static ItemAttribute SPEED()
 * @method static ItemAttribute DEFENSE()
 * @method static ItemAttribute DAMAGE()
 */
final class SkyBlockItemAttributes {
	use RegistryTrait;

	protected static function setup(): void {
		$factory = SkyBlockItemAttributeFactory::getInstance();

		self::register('strength', $factory->get('strength'));
		self::register('intelligence', $factory->get('intelligence'));
		self::register('critical_chance', $factory->get('critical chance'));
		self::register('critical_damage', $factory->get('critical damage'));
		self::register('fishing_speed', $factory->get('fishing speed'));
		self::register('foraging_fortune', $factory->get('foraging fortune'));
		self::register(
			'sea_creature_chance',
			$factory->get('sea_creature_chance')
		);
		self::register('mining_fortune', $factory->get('mining_fortune'));
		self::register('combat_wisdom', $factory->get('combat_wisdom'));
		self::register('foraging_wisdom', $factory->get('foraging_wisdom'));
		self::register('mining_wisdom', $factory->get('mining wisdom'));
		self::register('mining_speed', $factory->get('mining speed'));
		self::register('health', $factory->get('health'));
		self::register('speed', $factory->get('speed'));
		self::register('defense', $factory->get('defense'));
		self::register('damage', $factory->get('damage'));
	}

	protected static function register(
		string $name,
		ItemAttribute $item
	): void {
		self::_registryRegister($name, $item);
	}
}
