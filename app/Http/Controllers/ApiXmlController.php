<?php

namespace App\Http\Controllers;

use App\Models\Info;
use SimpleXMLElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            $this->data[$i]['lien'] = $value->link->__toString();
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

    public function SaveToDB() : void
    {
        $datas = self::getData();
        foreach($datas as $data)
        {
        
         $info = Info::where('lien',$data['lien'])->first();
         if(!$info){
            $save =Info::create([
                'titre' => $data['titre'],
                'description' => $data['description'],
                'lien' => $data['lien'],
                'date_pub' => $data['date_pub'],
                'image' => json_encode($data['image']),
            ]);
        }
    }
    $infos =Info::all();
    }

    public function getNews($page = 1, $perPage = 15)
    {
        // self::SaveToDB();
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
    public function getInfo(String $titre)
    {
        $info = '';
        $data = self::getData();
        foreach($data as $da){
            if($da['titre'] == $titre){
                $info = $da;
            }
        }
        return response()->json([
            'data' => $info
        ]);
    }
    public function setInfo(Request $req)
    {
        dd($req);
    }
}
