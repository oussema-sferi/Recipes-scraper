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
        $finalResults = $this->helper->getAllRecipesData('recipes_links.txt');
        $this->databaseOperations->createDB();
        $this->databaseOperations->createTable();
        foreach ($finalResults as $oneRecipe){
            if(array_key_exists("images",$oneRecipe))
            {
                $this->databaseOperations->insertDataDB($oneRecipe["link"], $oneRecipe["title"], $oneRecipe["description"], json_encode($oneRecipe["ingredients"], JSON_UNESCAPED_SLASHES), json_encode($oneRecipe["energy_value_per_serving"], JSON_UNESCAPED_SLASHES), json_encode($oneRecipe["instructions_steps"], JSON_UNESCAPED_SLASHES), json_encode($oneRecipe["images"], JSON_UNESCAPED_SLASHES));
            } else {
                $this->databaseOperations->insertDataDB($oneRecipe["link"], $oneRecipe["title"],$oneRecipe["description"], json_encode($oneRecipe["ingredients"], JSON_UNESCAPED_SLASHES), json_encode($oneRecipe["energy_value_per_serving"], JSON_UNESCAPED_SLASHES), json_encode($oneRecipe["instructions_steps"], JSON_UNESCAPED_SLASHES));
            }
        }
    }
}