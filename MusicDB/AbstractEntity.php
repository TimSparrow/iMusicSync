<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MusicDB;

/**
 * Description of AbstractEntity
 *
 * @author timofey
 */
abstract class AbstractEntity
{
	private $attributes;
	protected $pdo;
	public function __construct()
	{
		$this->attributes = Array();
		$this->pdo = \ImportCommand::getPdo();
	}

	/**
	 * magic accessor is required for PDO fetch
	 * @param String $name
	 * @return Mixed
	 */
	public function __get($name)
	{
		return $this->attributes[$name];
	}

	/**
	 * magic accessor is required by PDO::FETCH_OBJECT
	 * @param String $name
	 * @param Mixed $value
	 */
	public function __set($name, $value)
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * Returns a string representation of the entity suitable for file name component
	 * Should be reasonably sortable (i.e. albums start with year, tracks with number
	 * @return String
	 */
	public abstract function getPathName();

	/**
	 * Replace all special characters with '-'
	 * Used within getPathName()
	 * @param String $name
	 * @return String
	 */
	public static function normalize($name)
	{
		// replace potentially unsafe chars with '-'
		$s =  preg_replace('/(\s|\.|,|\:|\\|\/|\(|\)|\'|\`)+/', '-', $name);
		// replace multiple '-' with single ones
		$s = preg_replace('/\-+/', '-', $s);
		$s = trim($s, '-');
		return $s;
	}
}
