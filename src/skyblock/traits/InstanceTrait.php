<?php

declare(strict_types=1);

namespace skyblock\traits;

trait InstanceTrait {

	private static self $instance;

	/**
	 * @return static
	 */
	public static function getInstance() : static{
		return self::$instance;
	}
}