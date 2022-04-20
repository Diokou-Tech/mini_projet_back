<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class ApiXmlController extends Controller
{
    public $url_default = 'www.lemonde.fr/rss/en_continu.xml';
    public $data = array();
    public function SendRequete(string $url)
    {
        // dd(openssl_get_cert_locations());
        $response = Http::get($url);
        return $response;
    }
    public function getData()
    {
        $response = self::SendRequete($this->url_default);
        $response = ($response->body());
        $xml = new SimpleXMLElement($response);
        // la source
        $source['titre'] = $xml->channel->title->__toString();
        $source['description'] = $xml->channel->description->__toString();
        $source['copyright'] = $xml->channel->copyright->__toString();
        $source['lien'] = $xml->channel->link->__toString();
        $source['date_pub'] = $xml->channel->pubDate->__toString();
        $source['langue'] = $xml->channel->language->__toString();
        // recuperer les données
        $i=0;
        foreach( $xml->channel->item as $value){
            $this->data[$i]['titre'] = $value->title->__toString();
            $this->data[$i]['description'] = $value->description->__toString();
            $this->data[$i]['date_pub'] = $value->pubDate->__toString();
            $this->data[$i]['id'] = $i;
            // image
            $content = $value->children('media', true)->content;
            $contentattr = $content->attributes();
            foreach($contentattr as $name => $value){
            // echo $name . ' = '.  $value . '<br />';
            $this->data[$i]['image'][$name] = $value->__toString();
            }
            $i++;
        }
        return $this->data;
    }

    public function getNews($page = 1, $perPage = 10)
    {
        $data = self::getData();
        $dataReported = array_chunk($data,$perPage,true);
        $dataCurrent = $dataReported[$page-1];
        return response()->json([
            'total' => count($data),
            'perPage' => $perPage,
            'lastPage' => count($dataReported),
            'page' => $page,
            'data' => $dataCurrent
        ]);
    }
}
