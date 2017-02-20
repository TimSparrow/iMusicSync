<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\MusicDB;
use TimSparrow\DB;

/**
 * Description of AbstractEntity
 *
 * @author timofey
 */
abstract class AbstractEntity
{
	private $attributes;

	public function __construct()
	{
		if(!is_array($this->attributes))
		{
			$this->attributes = Array();
		}

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

	public function getAttributes()
	{
		return $this->attributes;
	}
	/**
	 * Returns a string representation of the entity suitable for file name component
	 * Should be reasonably sortable (i.e. albums start with year, tracks with number
	 * @return String
	 */
	public abstract function getPathName();


	/**
	 * Return a subset of Id3 tags relevant to this entity
	 * @param int $version version of id3 tags to return
	 * @return Array
	 */
	public abstract function getId3Tags($version=2);


	/**
	 * returns suitable string representation for listing/logging/debug
	 */
	public abstract function __toString();

	/**
	 * returns unique identifier for this entity used in db
	 */
	public abstract function getId();

	/**
	 * Replace all special characters with '-'
	 * Used within getPathName()
	 * @param String $name
	 * @return String
	 */
	public static function normalize($name)
	{
		// replace potentially unsafe chars with '-'
		$s =  preg_replace('/(\s|\t|\.|,|\:|\\|\/|\(|\)|\'|\`)+/', '-', $name);
		// replace multiple '-' with single ones
		$s = preg_replace('/\-+/', '-', $s);
		$s = trim($s, '-');
		return $s;
	}
}
