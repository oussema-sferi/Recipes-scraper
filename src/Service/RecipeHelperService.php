<?php
namespace App\Services;
use App\Database\DatabaseOperations;
use Goutte\Client;
use PDO;
use PDOException;

class RecipeHelperService
{
    //const PATH = 'C:/Users/TESTLOCALPATH/Downloads/recipes/';
    const URL_PATH_TEST = 'http://cakesland.ru/downloads/';
    const PATH = "/home/test/Downloads/recipes/"; // change your images download path here
    private $db;
    public function __construct(private Client $client) {
        $this->db = new DatabaseOperations();
    }
    private array $images;
    private array $energies;
    private array $ingredients;
    private array $instructionsSteps;
    private array $results;
    private array $allResults = [];
    private int $counter = 0;
    private string $randomString;
    private string $newFolder;
    public function getOneRecipeData($oneRecipe)
    {
        $this->results = [];
        $this->images = [];
        $this->energies = [];
        $this->ingredients = [];
        $this->instructionsSteps = [];

        $crawler = $this->client->request('GET', $oneRecipe);

        // check if 404 not found page
        if($crawler->filter('.emotion-10c8urk')->count() > 0)
        {
            if(trim($crawler->filter('.emotion-10c8urk')->text()) === "404")
            {
                return null;
            }
        }

        // link
        $this->results["link"] = $oneRecipe;

        // title
        if($crawler->filter('.emotion-gl52ge')->count() > 0)
        {
            $recipeTitle = $crawler->filter('.emotion-gl52ge')->text();
            //if($recipeTitle) $this->results["title"] = $recipeTitle
            $this->results["title"] = $recipeTitle;
        } else {
            $this->results["title"] = null;
        }

        // description
        if($crawler->filter('.emotion-1x1q7i2')->count() > 0)
        {
            $recipeDescription = $crawler->filter('.emotion-1x1q7i2')->text();
            $this->results["description"] = $recipeDescription;
        } else {
            $this->results["description"] = null;
        }

        // images
        if($crawler->filter('div .emotion-1voj7e4 > button')->count() > 0) {
            $this->randomString = md5(uniqid());
            $this->newFolder = self::PATH . $this->randomString;
            if(!is_dir($this->newFolder)){
                mkdir($this->newFolder);
            }
            $crawler->filter('div .emotion-1voj7e4 > button')->each(function ($node) {
                $imageRandomString = md5(uniqid());
                if ($node->filter('img')->count() > 0) {
                    $imageUrl = $node->filter('img')->attr('src');
                    $givenImageSize = explode('/', $imageUrl)[5];
                    $imageUrl = str_replace($givenImageSize, '250x-', $imageUrl);
                    $name = pathinfo(parse_url($imageUrl)['path'], PATHINFO_FILENAME);
                    $ext = pathinfo(parse_url($imageUrl)['path'], PATHINFO_EXTENSION);
                    $img = $this->newFolder . '/' . $imageRandomString . $name . '.' . $ext;
                    $newImageUrl = self::URL_PATH_TEST . $this->randomString . '/' . $imageRandomString . $name . '.' . $ext;
                    $this->images[] = $newImageUrl;
                    file_put_contents($img, file_get_contents($imageUrl));
                }
            });
        }
        if($this->images) {
            $this->results["images"] = $this->images;
        } else {
        $this->results["images"] = null;
        }


        // ingredients
        if($crawler->filter('.emotion-1047m5l')->count() > 0)
        {
            $this->ingredients["portions"] = $crawler->filter('.emotion-1047m5l')->text();
        }
        if($crawler->filter('.emotion-13pa6yw > .emotion-7yevpr')->count() > 0)
        {
            $crawler->filter('.emotion-13pa6yw > .emotion-7yevpr')->each(function ($node) {
                $this->ingredients["ingredients"][] = [$node->filter('.emotion-bjn8wh')->text() => $node->filter('.emotion-15im4d2')->text()];
            });
        }

        $this->results["ingredients"] = $this->ingredients;

        // energy value per serving
        if($crawler->filter('.emotion-13pa6yw > .emotion-8fp9e2')->count() > 0)
        {
            $crawler->filter('.emotion-13pa6yw > .emotion-8fp9e2')->each(function ($node) {
                if ($node->filter('span')->count() > 0)
                    $this->energies[trim($node->filter('span')->attr('itemprop'))] = trim($node->text());
            });

        }
        if($this->energies) {
            $this->results["energy_value_per_serving"] = $this->energies;
        } else {
            $this->results["energy_value_per_serving"] = null;
        }


        // instructions steps
        if($crawler->filter('.emotion-1ywwzp6 > div')->count() > 0)
        {
            $crawler->filter('.emotion-1ywwzp6 > div')->each(function ($node) {
                if($node->filter('div')->attr('itemprop') && $node->filter('div')->attr('itemprop') === 'recipeInstructions')
                {
                    $this->instructionsSteps[] = $node->text();
                }
            });
            $this->results["instructions_steps"] = $this->instructionsSteps;
        }
        if($this->instructionsSteps) {
            $this->results["instructions_steps"] = $this->instructionsSteps;
        } else {
            $this->results["instructions_steps"] = null;
        }
        // final result
        return $this->results;
    }

    public function getAllRecipesData($file)
    {
        $limitNumberOfLinks = 200; // Here you modify after how many links the data should be inserted to the db
        $contents = file($file);
        foreach($contents as $line) {
            if(isset($line))
            {
                $this->allResults[]= $this->getOneRecipeData(trim($line));
                $this->counter++;
                var_dump($this->counter);
            }
            if(($this->counter % $limitNumberOfLinks) === 0)
            {
                $this->newFile = $this->insertData($file);
                $this->counter = 0;
            }
        }
        $this->insertionLoop();
        file_put_contents($file, '');
        $this->allResults = [];
        //if($this->newFile !== null) $this->insertData($this->newFile);
        //return $this->allResults;
    }

    public function insertData($file)
    {
        $contents = file($file);
        $this->insertionLoop();
        array_splice($contents, 0, $this->counter);
        file_put_contents($file, $contents);
        $this->allResults = [];
        return $file;
    }

    public function insertionLoop()
    {
        foreach ($this->allResults as $oneRecipe){
            if($oneRecipe !== null)
            {
                if(array_key_exists("images",$oneRecipe))
                {
                    $this->db->insertDataDB($oneRecipe["link"], $oneRecipe["title"], $oneRecipe["description"], json_encode($oneRecipe["ingredients"],  JSON_UNESCAPED_UNICODE), json_encode($oneRecipe["energy_value_per_serving"],  JSON_UNESCAPED_UNICODE), json_encode($oneRecipe["instructions_steps"],  JSON_UNESCAPED_UNICODE), json_encode($oneRecipe["images"], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                } else {
                    $this->db->insertDataDB($oneRecipe["link"], $oneRecipe["title"],$oneRecipe["description"], json_encode($oneRecipe["ingredients"], JSON_UNESCAPED_UNICODE), json_encode($oneRecipe["energy_value_per_serving"], JSON_UNESCAPED_UNICODE), json_encode($oneRecipe["instructions_steps"],  JSON_UNESCAPED_UNICODE));
                }
            }
        }
    }
}