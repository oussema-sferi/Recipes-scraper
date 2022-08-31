<?php
namespace App\Controller;
use App\Services\RecipeHelperService;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use GuzzleHttp\Client as GuzzleClient;

class RecipeScraper
{
    public function getData()
    {
        $client = new Client();
        $helper = new RecipeHelperService($client);
        //$crawler = $client->request('GET', 'https://eda.ru/recepty/vypechka-deserty/domashniy-chizkeyk-s-tvorogom-i-syrom-rikkota-54112');
        //$helper->getAllRecipesData('recipes_links.txt');

        print_r($helper->getAllRecipesData('recipes_links.txt'));
    }
}