<?php

declare(strict_types=1);

namespace skyblock\items\itemattribute;

use InvalidArgumentException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;

trait ItemAttributeTrait {
	/** @var ItemAttributeInstance[] */
	private array $attributes = [];

	/**
	 * @return ItemAttributeInstance[]
	 */
	public function getItemAttributes(): array {
		$tag = $this->getNamedTag()->getCompoundTag('tag_attributes');

		if ($tag === null) {
			return [];
		}

		$arr = [];

		$comp = $this->getAllExtraAttributes();
		foreach ($tag->getValue() as $tagName => $tag) {
			if ($tag instanceof FloatTag) {
				$attribute = SkyBlockItemAttributeFactory::getInstance()->get(
					$tagName
				);

				if ($attribute) {
					$arr[$attribute->getName()] = new ItemAttributeInstance(
						$attribute,
						$tag->getValue()
					);
				}
			}
		}

		foreach ($comp as $list) {
			/** @var ItemAttributeInstance $instance */
			foreach ($list as $instance) {
				$n = $instance->getAttribute()->getName();
				if (isset($arr[$instance->getAttribute()->getName()])) {
					$arr[$n] = new ItemAttributeInstance(
						$instance->getAttribute(),
						$arr[$n]->getValue() + $instance->getValue()
					);
				} else {
					$arr[$n] = new ItemAttributeInstance(
						$instance->getAttribute(),
						$instance->getValue()
					);
				}
			}
		}

		return $arr;
	}

	public function getItemAttribute(
		ItemAttribute $attribute
	): ItemAttributeInstance {
		$s = new ItemAttributeInstance(
			$attribute,
			(
				$this->getNamedTag()->getCompoundTag('tag_attributes') ??
				new CompoundTag()
			)->getFloat($attribute->getName(), 0)
		);

		foreach ($this->getAllExtraAttributes() as $list) {
			if (isset($list[$attribute->getName()])) {
				$s = new ItemAttributeInstance(
					$attribute,
					$s->getValue() + $list[$attribute->getName()]->getValue()
				);
			}
		}

		return $s;
	}

	public function setItemAttribute(ItemAttributeInstance $instance): self {
		$attribute = $instance->getAttribute();

		if (
			$instance->getValue() > $attribute->getMaxValue() ||
			$instance->getValue() < $attribute->getMinValue()
		) {
			throw new InvalidArgumentException(
				"{$attribute->getName()} Item Attribute can have values between {$attribute->getMinValue()}-{$attribute->getMaxValue()} given: {$instance->getValue()}"
			);
		}

		$tag =
			$this->getNamedTag()->getCompoundTag('tag_attributes') ??
			new CompoundTag();
		$tag->setFloat(
			$instance->getAttribute()->getName(),
			$instance->getValue()
		);

		$this->getNamedTag()->setTag('tag_attributes', $tag);
		$this->resetLore();

		return $this;
	}

	public function addExtraAtribute(
		string $extraName,
		ItemAttributeInstance $attribute
	): self {
		$tag =
			$this->getNamedTag()->getCompoundTag('tag_extra_attributes') ??
			new CompoundTag();
		$compound = $tag->getCompoundTag($extraName) ?? new CompoundTag();
		$compound->setFloat(
			$attribute->getAttribute()->getName(),
			$attribute->getValue()
		);
		$tag->setTag($extraName, $compound);

		$this->getNamedTag()->setTag('tag_extra_attributes', $tag);

		return $this;
	}

	/**
	 * @return ItemAttributeInstance[]
	 */
	public function getAllExtraAttributes(): array {
		$tag = $this->getNamedTag()->getCompoundTag('tag_extra_attributes');
		if ($tag === null) {
			return [];
		}

		$arr = [];
		foreach ($tag->getValue() as $key => $compound) {
			if (!$compound instanceof CompoundTag) {
				continue;
			}

			foreach ($compound->getValue() as $tagName => $tag) {
				if ($tag instanceof FloatTag) {
					$attribute = SkyBlockItemAttributeFactory::getInstance()->get(
						$tagName
					);

					if ($attribute) {
						$arr[$key][
							$attribute->getName()
						] = new ItemAttributeInstance(
							$attribute,
							$tag->getValue()
						);
					}
				}
			}
		}

		return $arr;
	}

	public function getExtraAttributeByName(
		string $extraName,
		ItemAttribute $attribute
	): ItemAttributeInstance {
		$list = $this->getAllExtraAttributesByName($extraName);

		return $list[$attribute->getName()] ??
			new ItemAttributeInstance($attribute, 0);
	}

	public function getAllExtraAttributesByName(string $extraName): array {
		$tag = $this->getNamedTag()->getCompoundTag('tag_extra_attributes');
		if ($tag === null) {
			return [];
		}

		$compound = $tag->getCompoundTag($extraName) ?? new CompoundTag();

		$arr = [];
		foreach ($compound->getValue() as $tagName => $tag) {
			if ($tag instanceof FloatTag) {
				$attribute = SkyBlockItemAttributeFactory::getInstance()->get(
					$tagName
				);

				if ($attribute) {
					$arr[$attribute->getName()] = new ItemAttributeInstance(
						$attribute,
						$tag->getValue()
					);
				}
			}
		}

		return $arr;
	}
}
