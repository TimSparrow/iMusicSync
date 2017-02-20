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

	private function init()
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
		return $this->getFullPath(Config::get('iPhoneDir'));
	}

	

	private function saveTrack($track, $path)
	{
		$trackFile = $this->getSourcePath().$track->getMediaFile();
		$targetFile = $path.'/'.$track->getPathName();

		if(is_file($targetFile))
		{
			if(filemtime($targetFile) >= filemtime($trackFile))
			{
				$this->writeln('target exists, skipping');
				return true;
			}
			else {
				$this->write('target is old, overwriting');
			}
		}
		// write after this point
		$this->write(" copying...");
		$command = sprintf($this->cmd, $trackFile, $targetFile);
		system($command, $res);
		if($this->useRecode)
		{
			$this->updateTags($targetFile);
		}

		$this->writeln("done");
	}


	private function updateTags($file, $a)
	{
		$id3Frames = array_merge($this->track->getId3Tags(), $this->album->getId3Tags(), $this->artist->getId3Tags(), $this->getId3Tags());
		$idManager = new Zend_Media_Id3v2($file);
		foreach($id3Frames as $frame => $content)
		{
			$frameClass = 'Zend_Media_Id3_Frame_'.$frame;
			$frameObject = new $frameClass;
			$frameObject->setText($content);
			$idManager->addFrame($frameObject);
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
		$this->init();
		try
		{
			$this->albums = Album::getList();
			$this->writeln(sprintf("Got %d albums", sizeof($this->albums)));
			foreach($this->albums as $this->album)
			{
				$this->artist = $this->album->getArtist();
				$this->write($this->artist . ' // '. $this->album);
				$tracks = $this->album->getTracks();
				$path = $this->createPathForTracks(Array($this->artist->getPathName(), $this->album->getPathName()));
				$this->writeln(sprintf("::Tracks:%d", sizeof($tracks)));
				foreach($tracks as $this->track)
				{
					$this->write("\t".$track." --> ");
					$this->saveTrack($this->track, $path);
				}
				exit;
			}
		}
		catch (\PDOException $x)
		{
			$this->writerr("Database exception: ".$x->getMessage());
			$this->writerr(print_r($x->errorInfo, true));
			$this->writerr($x->getTraceAsString());
			exit;
		}
	}
}
