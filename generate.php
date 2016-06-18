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

require_once 'vendor/autoload.php';

require_once 'RepoParsers/github.php';

$twigLoader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($twigLoader, array(
));

$services = [];
$parsers = [];

foreach ($_POST["services"] as $service)
{
    if (empty($service["name"]) || empty($service["username"]))
        continue;

    $class = $service["name"] . "RepoParser";
    $parser = new $class($service["username"]);

    $repoService["name"] = $parser->getIdentifier();
    $repoService["username"] = $service["username"];
    $repoService["url"] = $parser->getProfileUrl($repoService["username"]);
    $services[] = $repoService;
    $parsers[] = $parser;
}

$namedRepositories = [];
$namedLanguages = [];

foreach ($parsers as $parser)
{ 
    // Get all information. Initially, we store this as an associative array
    // based on the repository name to group repositories from several sources
    // with the same name
    foreach ($parser->getRepos() as $repo => $data)
    {
        $repoName = $parser->getName($repo);
        $repoHomepage = $parser->getHomepage($repo);
        $repoRepo = $parser->getRepositoryUrl($repo);
        $repoDescription = $parser->getDescription($repo);
        $repoLanguage = $parser->getLanguage($repo);
        $repoCreated = $parser->getCreationDate($repo);
        $repoUpdated = $parser->getLastUpdatedDate($repo);
        $repoStars = $parser->getStarCount($repo);
        $repoWatchers = $parser->getWatcherCount($repo);
        $repoForks = $parser->getForkCount($repo);

        if (!array_key_exists($repoName, $namedRepositories))
            @$namedLanguages[$repoLanguage]++;

        $namedRepositories[$repoName]["homepage"] = $repoHomepage;
        $namedRepositories[$repoName]["repo"] = $repoRepo;
        $namedRepositories[$repoName]["description"] = $repoDescription;
        $namedRepositories[$repoName]["language"] = $repoLanguage;
        $namedRepositories[$repoName]["created"] = $repoCreated;
        $namedRepositories[$repoName]["updated"] = $repoUpdated;
        @$namedRepositories[$repoName]["stars"] += $repoStars;
        @$namedRepositories[$repoName]["watchers"] += $repoWatchers;
        @$namedRepositories[$repoName]["forks"] += $repoForks;
    }
}

// Convert repositories to a normal array for sorting
$repositories = [];
foreach ($namedRepositories as $name => $data)
{
    $repo = $namedRepositories[$name];

    $repo["name"] = $name;
    $repositories[] = $repo;
}

// Sort repositories on popularity
usort($repositories, function($a, $b) {
    return $b["stars"] + $b["watchers"] + $b["forks"] <=> $a["stars"] + $a["watchers"] + $a["forks"];
});

// Convert languages to a normal array so twig can sort it
$languages = [];
foreach ($namedLanguages as $name => $data)
{
    $language = ['name' => $name,
                 'count' => $namedLanguages[$name]];
    $languages[] = $language;
}

$name = $_POST['name'];

echo $twig->render('report.erb', array('name' => $name,
                                      'services' => $services,
                                      'languages' => $languages,
                                      'repositories' => $repositories));

