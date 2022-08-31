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
        $myfileReqults = fopen("results1.txt", "w");
        $helper = new RecipeHelperService($client);
        //$crawler = $client->request('GET', 'https://eda.ru/recepty/vypechka-deserty/domashniy-chizkeyk-s-tvorogom-i-syrom-rikkota-54112');
        //$helper->getAllRecipesData('recipes_links.txt');


        file_put_contents('results1.txt', print_r($helper->getAllRecipesData('recipes_links.txt'), true));
    }
}