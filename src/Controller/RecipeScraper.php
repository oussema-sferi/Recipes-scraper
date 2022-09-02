<?php
namespace App\Controller;
use App\Services\RecipeHelperService;
use Goutte\Client;
use App\Database\DatabaseOperations;
require_once 'src/Database/DatabaseOperations.php';

class RecipeScraper
{
    private Client $client;
    private RecipeHelperService $helper;
    private DatabaseOperations $databaseOperations;
    public function __construct() {
        $this->client = new Client();
        $this->helper = new RecipeHelperService($this->client);
        $this->databaseOperations = new DatabaseOperations();
    }
    public function getData()
    {
        $this->databaseOperations->createTable();
        $this->helper->getAllRecipesData('recipes_links.txt');
        //$this->databaseOperations->createDB();
    }
}