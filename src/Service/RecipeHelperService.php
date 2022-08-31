<?php
namespace App\Services;
use Goutte\Client;

class RecipeHelperService
{
    public function __construct(private Client $client) {

    }
    const DOMAIN = 'https://elements.envato.com/sign-in';
    private array $brandsUrlsArray;
    private array $productsUrlsArray;
    private array $singlePageResults;
    private array $images;
    private array $energies;
    private array $ingredients;
    private array $instructionsSteps;
    private array $results;
    private array $allResults = [];
    private int $counter = 0;
    public function getOneRecipeData($oneRecipe)
    {
        $this->results = [];
        $this->images = [];
        $this->energies = [];
        $this->ingredients = [];
        $this->instructionsSteps = [];
        /*var_dump($oneRecipe);
        die();*/
        $crawler = $this->client->request('GET', $oneRecipe);
        // link
        $this->results["link"] = $oneRecipe;

        // title
        if($crawler->filter('.emotion-gl52ge')->count() > 0)
        {
            $recipeTitle = $crawler->filter('.emotion-gl52ge')->text();
            $this->results["title"] = $recipeTitle;
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
            $crawler->filter('div .emotion-1voj7e4 > button')->each(function ($node) {
                if ($node->filter('img')->count() > 0) {
                    $path = 'C:/Users/ITStacks/Downloads/recipes/';
                    $newFolder = $path . $this->results["title"];
                    if(!is_dir($newFolder)){
                        mkdir($newFolder);
                    }
                    $imageUrl = $node->filter('img')->attr('src');
                    $this->images[] = $imageUrl;
                    $name = pathinfo(parse_url($imageUrl)['path'], PATHINFO_FILENAME);
                    $ext = pathinfo(parse_url($imageUrl)['path'], PATHINFO_EXTENSION);
                    $img = $newFolder . '/' . md5(uniqid()) . $name . '.' . $ext;
                    file_put_contents($img, file_get_contents($imageUrl));
                }
            });
        }
        if($this->images) $this->results["images"] = $this->images;

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
            $this->results["energy_value_per_serving"] = $this->energies;
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

        /*print_r($results);
        die();*/
        return $this->results;
    }

    public function getAllRecipesData($file)
    {
        $client = new Client();
        $filename = $file;
        $contents = file($filename);
        foreach($contents as $line) {
            $this->counter++;
            $this->allResults[]= $this->getOneRecipeData(trim($line));
        }
        var_dump($this->counter);
        return $this->allResults;
    }
}