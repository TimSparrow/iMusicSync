<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\Commands;
use TimSparrow\MusicDB\Artist;
use TimSparrow\MusicDB\Album;
use TimSparrow\MusicDB\Track;
use TimSparrow\Config;
use TimSparrow\DB;

/**
 * Export iPhone music database to filesystem
 * Files are converted to mp3 format using ffmpeg
 * ID3 tags are recreated from database
 * Files are grouped by album, Albums are grouped by artists
 * Audio books and classical works not supported
 *
 * @author TimSparrow
 */
class ExportCommand extends \ConsoleKit\Command
{
	private $albums;
	private $album;
	private $artist;
	private $track;
	private $targetPath;

	/**
	 * Wrapper to get config value
	 * @param String $name
	 * @return Mixed
	 */
	private function getConfig($name)
	{
		return Config::get($name);
	}

	/**
	 * Constructs and returns a path to save tracks for a given album
	 * @param array $pathComponents - array like artist, album, etc.
	 * @return string - path
	 * @throws \Exception if dir creation fails
	 */
	private function createPathForTracks(Array $pathComponents)
	{
		$exportPath = Config::getFullPath($this->targetPath) . '/' . implode('/', $pathComponents);
		if(!is_dir($exportPath))
		{
			if(!mkdir($exportPath, Config::get(exportDirMode), true))
			{
				throw new \Exception("Failed to create directory $exportPath");
			}
		}
		return $exportPath;
	}

	private function init(Array $args, Array $options)
	{
		DB::init();
		$this->targetPath = $this->getConfig('exportTargetPath');
		$this->initCmd();
	}

	private function initCmd()
	{
		if(Config::get('useRecode'))
		{
			$this->cmd = Config::get('cmdRecode');
			$this->writeln("Recode mode selected");
		}
		elseif(Config::get('useLinks'))
		{
			$this->cmd = Config::get('cmdLink');
			$this->writeln("Hard link mode selected");
		}
		else
		{
			$this->cmd = Config::get('cmdCopy');
			$this->writeln("Copy mode selected");
		}
	}

	private function getSourcePath()
	{
		return Config::getFullPath(Config::get('iPhoneDir'));
	}

	

	private function saveTrack(Track $track, $path)
	{
		$trackFile = $this->getSourcePath().$track->getMediaFile();
		$targetFile = $path.'/'.$track->getPathName();
		if($this->isSaveTrackNeeded($trackFile, $targetFile))
		{
			// write after this point
			$this->write(" copying...");
			$command = sprintf($this->cmd, $trackFile, $targetFile);
			$res = 0;
			system($command, $res);
			if($res > 0)
			{
				throw new \Exception(sprintf("Error: command %s returned error code %d", $command, $res));
			}
			$this->write('; ');
			if(!Config::get('useLinks'))
			{
				$this->updateTags($targetFile);
			}
			$this->writeln("done");
		}
	}

	private function isSaveTrackNeeded($trackFile, $targetFile)
	{
		if(is_file($targetFile))
		{
			if(Config::get('overwrite') == 'none')
			{
				$this->write('overwrite disabled, skipping');
			}
			elseif(filemtime($targetFile) >= filemtime($trackFile))
			{
				$this->writeln('target exists, skipping');
				return false;
			}
			elseif(Config::get('overwrite') == 'all')
			{
				$this->write('overwrite is forced');
			}
			else {
				$this->write('target is old, overwriting');
			}
		}
		else {
			$this->write('writing');
		}
		return true;
	}


	private function updateTags($file)
	{
		$id3Frames = array_merge($this->track->getId3Tags(), $this->album->getId3Tags(), $this->artist->getId3Tags(), $this->getId3Tags());
		

		$this->write('Updating ID3 tags');
		print_r($id3Frames);
		exit;

		$idManager = new \Zend_Media_Id3v2($file);
		foreach($id3Frames as $frame => $content)
		{
			// check if frame exists
			$currentFrames = $idManager->getFramesByIdentifier(strtoupper($frame));
			if(sizeof($currentFrames) > 0)
			{
				if(strtolower($frame) != 'tenc') // do not update encoder
				{
					$currentFrames[0]->setText($content);
				}
			}
			else
			{
				$frameClass = '\\Zend_Media_Id3_Frame_'.$frame;
				$frameObject = new $frameClass;
				$frameObject->setText($content);
				$idManager->addFrame($frameObject);
			}
		}
		$idManager->write();
	}

	public function getId3Tags()
	{
		return Array('Tenc'	=> Config::getSoftware());
	}

	/**
	 * Required method by \ConsoleKit\Command
	 * @param array $args
	 * @param array $options
	 */
	public function execute(array $args, array $options = array())
	{
		$this->init($args, $options);
		$this->albums = Album::getList();
		$this->writeln(sprintf("Got %d albums", sizeof($this->albums)));
		foreach($this->albums as $this->album)
		{
			$this->artist = $this->album->getArtist();
			$this->write($this->artist . ' // '. $this->album);
			$this->processTracks($this->album->getTracks());
			exit;	// debug - process one album only
		}

	}

	private function processTracks($tracks)
	{
		$path = $this->createPathForTracks(Array($this->artist->getPathName(), $this->album->getPathName()));
		$this->writeln(sprintf("::Tracks:%d", sizeof($tracks)));
		foreach($tracks as $this->track)
		{
			$this->write("\t".$this->track." --> ");
			$this->saveTrack($this->track, $path);
		}
	}
}
