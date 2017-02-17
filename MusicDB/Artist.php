<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MusicDB;

/**
 * Container for an Artist entity
 *
 * @author timofey
 */
class Artist extends AbstractEntity
{
	public function getPathName()
	{
		return $this->normalize($this->album_artist);
	}


	/**
	 * Gets artist object for a given album
	 * @return Artist
	 */
	public static function getArtist($id)
	{
		$query = "SELECT * FROM album_artist WHERE album_artist_pid=?";
		$stm = $this->pdo->prepare($query);
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class());
		if ($stm->execute(Array($id)))
		{
			return $stm->fetch();
		}
	}
}
