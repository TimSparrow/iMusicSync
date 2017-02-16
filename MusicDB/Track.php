<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MusicDB;

/**
 * Description of Track
 *
 * @author timofey
 */
class Track extends AbstractEntity
{
	public function getPathName()
	{
		return sprintf("%d_%02d_%s",  $this->disc_number, $this->track_number, $this->title);
	}

	public function getFile()
	{
		return $this->path . '/'.$this->filename;
	}

}
