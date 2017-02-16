<?php
namespace MusicDB;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Album extends AbstractEntity{
	public function getPathName()
	{
		return $this->album_year . '_' . $this->normalize($this->album);
	}

	/**
	 * Gets artist object for a given album
	 * @return Artist
	 */
	public function getArtist()
	{
		$query = "SELECT * FROM album_artist WHERE album_artist_pid=?";
		$stm = $this->pdo->prepare($query);
		$stm->setFetchMode(PDO::FETCH_CLASS, 'Artist');
		if ($stm->execute(Array($this->album_artist_id)))
		{
			return $stm->fetch();
		}
	}
}
