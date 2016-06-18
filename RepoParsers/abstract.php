<?php
/*
    This file is part of Resumer
    Copyright (C) 2016  Sylvia van Os

    Resumer is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

abstract class RepoParser
{
    protected function callApi(string $url, array $streamopts) : string {
        return file_get_contents($url, false, stream_context_create($streamopts));
    }

    abstract public function __construct(string $username);

    abstract public function getIdentifier() : string;
    abstract public function getProfileUrl(string $username) : string;

    abstract public function getRepos() : array;

    abstract public function getName(int $repo) : string;
    abstract public function getHomepage(int $repo) : string;
    abstract public function getRepositoryUrl(int $repo) : string;
    abstract public function getDescription(int $repo) : string;
    abstract public function getLanguage(int $repo) : string;
    abstract public function getStarCount(int $repo) : int;
    abstract public function getWatcherCount(int $repo) : int;
    abstract public function getForkCount(int $repo) : int;

    abstract public function getCreationDate(int $repo) : array;
    abstract public function getLastUpdatedDate(int $repo) : array;
}

