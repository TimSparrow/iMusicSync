<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\Commands;
use \TimSparrow;
use \TimSparrow\MusicDB;

/**
 * Import a music collection into iPhone music database
 *
 * @author timofey
 */
class ImportCommand extends \ConsoleKit\Command
{
	private $sourcePath;
	private $options;

	public function execute(array $args, array $options = array())
	{
		$this->init($args, $options);
		// scan source path
		$this->importTracks($options);
		//
		//
	}

	private function init(&$args, &$options)
	{
		if((count($args) <= 0) && (count($options) <= 0) || (in_array('help', $options)))
		{
			$this->printHelp();
		}
		$this->sourcePath = $args[1];
		$this->options = $options;
		DB::init();
	}

	private function importTracks(&$options)
	{
		$tracks = new \DirectoryIterator($this->sourcePath);
		foreach($tracks as $track)
		{
			$this->importSingleTrack($track);
		}
	}

	private function importSingleTrack($trackFile)
	{
		// get file metadata
		$trk = Track::initFromFile($trackFile);
		$artist = Artist::searchTag($trk) || Artist::create($trk);
		$album = Album::searchTag($trk, $artist);
	}

	public function printHelp()
	{
		echo "Usage: \$1 = source path";
		exit;
	}
}
