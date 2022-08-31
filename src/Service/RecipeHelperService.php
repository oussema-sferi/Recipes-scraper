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
    private array $allResults = [];
    private int $counter = 0;
    public function getOneRecipeData($oneRecipe)
    {
        $this->images = [];
        $this->energies = [];
        $this->ingredients = [];
        $this->instructionsSteps = [];
        /*var_dump($oneRecipe);
        die();*/
        $crawler = $this->client->request('GET', $oneRecipe);
        // link
        $results["link"] = $oneRecipe;

        // title
        if($crawler->filter('.emotion-gl52ge')->count() > 0)
        {
            $recipeTitle = $crawler->filter('.emotion-gl52ge')->text();
            $results["title"] = $recipeTitle;
        }



        // author
        if($crawler->filter('.emotion-847em2')->count() > 0) {
            $recipeauthor = $crawler->filter('.emotion-847em2')->text();
            $arr = explode(': ', $recipeauthor);
            $recipeauthor = $arr[1];
            $results["author"] = $recipeauthor;
        }



        // images
        if($crawler->filter('div .emotion-1voj7e4 > button')->count() > 0) {
            $crawler->filter('div .emotion-1voj7e4 > button')->each(function ($node) {
                if ($node->filter('img')->count() > 0) {
                    $this->images[] = $node->filter('img')->attr('src');
                }
            });
        }
        if($this->images) $results["images"] = $this->images;

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

        $results["ingredients"] = $this->ingredients;

        // energy value per serving
        if($crawler->filter('.emotion-13pa6yw > .emotion-8fp9e2')->count() > 0)
        {
            $crawler->filter('.emotion-13pa6yw > .emotion-8fp9e2')->each(function ($node) {
                if ($node->filter('span')->count() > 0)
                    $this->energies[$node->filter('span')->attr('itemprop')] = $node->text();
            });
            $results["energy_value_per_serving"] = $this->energies;
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
            $results["instructions_steps"] = $this->instructionsSteps;
        }

        /*print_r($results);
        die();*/
        return $results;
    }

    public function getAllRecipesData($file)
    {
        $client = new Client();
        $filename = $file;
        $contents = file($filename);
        foreach($contents as $line) {
            $this->counter++;
            $this->allResults[]= $this->getOneRecipeData(trim($line));
            /*var_dump($this->allResults);
            die();*/
            //echo $line . "\n";
        }
        var_dump($this->counter);
        return $this->allResults;
    }
}