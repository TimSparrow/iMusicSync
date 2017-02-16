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

	public function __get($name)
	{
		return $this->attributes[$name];
	}

	public function __set($name, $value)
	{
		$this->attributes[$name] = $value;
	}

	public abstract function getPathName();

	/**
	 * Replace all special characters with '-'
	 * @param String $name
	 * @return String
	 */
	public static function normalize($name)
	{
		return preg_replace('/(\s|\.|,|\:|\\|\/|\(|\)|\'|\`)+/', '-', $name);
	}
}
