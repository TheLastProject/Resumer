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

require_once 'abstract.php';

class GitHubRepoParser extends RepoParser
{
    private $repoData;

    private static $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "User-Agent: TheLastProject-Resumer\r\n"
        )
    );

    public function __construct(string $username) {
        $this->repoData = json_decode(parent::callApi(sprintf("https://api.github.com/users/%s/repos", $username), self::$opts), true);
        // Also grab organisation repositories
        $orgs = json_decode(parent::callApi(sprintf("https://api.github.com/users/%s/orgs", $username), self::$opts), true);
        foreach ($orgs as $org)
        {
            $this->repoData += json_decode(parent::callApi($org["repos_url"], self::$opts), true);
        }
    }

    public function getIdentifier() : string {
        return "GitHub";
    }

    public function getProfileUrl(string $username) : string {
        return sprintf("https://github.com/users/%s", $username);
    }

    public function getRepos() : array {
        return $this->repoData;
    }

    public function getName(int $repo) : string {
        return $this->repoData[$repo]["name"];
    }

    public function getHomepage(int $repo) : string {
        return $this->repoData[$repo]["homepage"] ?? "";
    }

    public function getRepositoryUrl(int $repo) : string {
        return $this->repoData[$repo]["html_url"] ?? "";
    }

    public function getDescription(int $repo) : string {
        return $this->repoData[$repo]["description"] ?? "No description";
    }

    public function getLanguage(int $repo) : string {
        return $this->repoData[$repo]["language"] ?? "Unknown";
    }

    public function getStarCount(int $repo) : int {
        return $this->repoData[$repo]["stargazers_count"];
    }

    public function getWatcherCount(int $repo) : int {
        return $this->repoData[$repo]["watchers_count"];
    }

    public function getForkCount(int $repo) : int {
        return $this->repoData[$repo]["forks_count"];
    }

    public function getCreationDate(int $repo) : array {
        return date_parse($this->repoData[$repo]["created_at"]);
    }

    public function getLastUpdatedDate(int $repo) : array {
        return date_parse($this->repoData[$repo]["updated_at"]);
    }
}

