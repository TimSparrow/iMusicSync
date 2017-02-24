<?php

/*
 * Copyright (C) 2017 TimSparrow
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace TimSparrow\MusicDB;

/**
 *
 * @author TimSparrow
 */
interface Id3Exportable
{
	/**
	 * Return a subset of Id3 tags relevant to this entity
	 * @param int $version version of id3 tags to return, assumed 2, ignored
	 * @return Array
	 */
	public abstract function getId3Tags($version=2);
}
