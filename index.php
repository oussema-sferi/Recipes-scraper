<?php
require 'vendor/autoload.php';
require_once 'src/Service/RecipeHelperService.php';
require_once 'src/Controller/RecipeScraper.php';
use App\Controller\RecipeScraper;
$scraper = new RecipeScraper();
$scraper->getData();
