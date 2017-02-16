<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MusicDB;

/**
 * Description of Artist
 *
 * @author timofey
 */
class Artist extends AbstractEntity
{
	public function getPathName()
	{
		return $this->normalize($this->album_artist);
	}
}
