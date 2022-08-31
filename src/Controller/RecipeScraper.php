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
        $helper = new RecipeHelperService();
        $crawler = $client->request('GET', 'https://eda.ru/recepty/vypechka-deserty/domashniy-chizkeyk-s-tvorogom-i-syrom-rikkota-54112');
        $res = $helper->getOneRecipeData($crawler);
        //file_put_contents('C:\Users\ITStacks\Downloads\test1.jpg', file_get_contents($res["images"][0]));
        print_r($res);
    }
}