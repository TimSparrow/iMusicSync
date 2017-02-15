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
}
