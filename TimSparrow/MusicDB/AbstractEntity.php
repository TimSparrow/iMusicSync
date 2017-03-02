<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\MusicDB;
use TimSparrow\DB;

/**
 * Abstract layer to represent a music database entity
 * Any entity such as album, track, etc should inherit this class
 *
 * @author TimSparrow
 */
abstract class AbstractEntity
{
	private $attributes;
	protected $artwork;

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

	/**
	 * Returns all stored attributes as array
	 * @return \Array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}


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

	/**
	 * retrieves artwork for current entity
	 * @return Artwork
	 */
	public function getArtwork()
	{
		if(null==$this->artwork)
		{
			try {
				$this->artwork = Artwork::getForEntity($this->getId());
			}
			catch (Exception $x)
			{
				// @todo Attempt to retrieve artwork elsewhere
				trigger_error(sprintf("Cannot get artwork for %s: %s", $this, $x->getMessage()), E_USER_WARNING);
				return null;
			}
		}
		return $this->artwork;
	}
}
