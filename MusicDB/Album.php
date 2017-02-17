<?php
namespace MusicDB;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Album extends AbstractEntity{

	const pattern = "%04d_%s";
	/**
	 * Returns
	 * @return String
	 */
	public function getPathName()
	{
		return sprintf(self::pattern, $this->album_year, $this->normalize($this->album));
	}

	/**
	 * Gets artist object for a given album
	 * @return Artist
	 */

	/**
	 * Returns db identifier
	 * @return \String
	 */
	public function getId()
	{
		return $this->album_pid;
	}

	public function __toString()
	{
		return $this->album;
	}

	/**
	 * Get all albums in the database
	 * @return \Traversable
	 */
	public static function getList()
	{
		$pdo = \ImportCommand::getPdo();
		$query = 'SELECT * FROM album ORDER BY sort_album';
		$albums = $pdo->query($query, \PDO::FETCH_CLASS, get_class());
		return $albums;
	}

	public function getTracks()
	{
		return Track::getList($this->getId());
	}

	public function getArtist()
	{
		return Artist::getById($this->album_artist_pid);
	}
	
}
