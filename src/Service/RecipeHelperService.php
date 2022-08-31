<?php
namespace App\Services;
use Goutte\Client;

class RecipeHelperService
{
    const DOMAIN = 'https://elements.envato.com/sign-in';
    private array $brandsUrlsArray;
    private array $productsUrlsArray;
    private array $singlePageResults;
    private array $images;
    private array $energies;
    private array $ingredients;
    private array $instructionsSteps;
    public function getOneRecipeData($crawler)
    {
        // title
        $recipeTitle = $crawler->filter('.emotion-gl52ge')->text();
        $results["title"] = $recipeTitle;

        // author
        $recipeauthor = $crawler->filter('.emotion-847em2')->text();
        $arr = explode(': ', $recipeauthor);
        $recipeauthor = $arr[1];
        $results["author"] = $recipeauthor;

        // images
        $crawler->filter('div .emotion-1voj7e4 > button')->each(function ($node) {
            if ($node->filter('img')->count() > 0) {
                $this->images[] = $node->filter('img')->attr('src');
            }
        });
        $results["images"] = $this->images;

        // ingredients
        $this->ingredients["portions"] = $crawler->filter('.emotion-1047m5l')->text();
        $crawler->filter('.emotion-13pa6yw > .emotion-7yevpr')->each(function ($node) {
            $this->ingredients["ingredients"][] = [$node->filter('.emotion-bjn8wh')->text() => $node->filter('.emotion-15im4d2')->text()];
        });
        $results["ingredients"] = $this->ingredients;

        // energy value per serving
        $crawler->filter('.emotion-13pa6yw > .emotion-8fp9e2')->each(function ($node) {
            $this->energies[$node->filter('span')->attr('itemprop')] = $node->text();
        });
        $results["energy_value_per_serving"] = $this->energies;

        // instructions steps
        $crawler->filter('.emotion-1ywwzp6 > div')->each(function ($node) {
            if($node->filter('div')->attr('itemprop') && $node->filter('div')->attr('itemprop') === 'recipeInstructions')
            {
                $this->instructionsSteps[] = $node->text();
            }

        });
        $results["instructions_steps"] = $this->instructionsSteps;
        return $results;
    }
}