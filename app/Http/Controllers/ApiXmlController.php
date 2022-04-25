<?php

namespace App\Http\Controllers;

use App\Models\Info;
use Carbon\Carbon;
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
        $i = 0;
        foreach ($xml->channel->item as $value) {
            $this->data[$i]['titre'] = $value->title->__toString();
            $this->data[$i]['description'] = $value->description->__toString();
            $this->data[$i]['date_pub'] = $value->pubDate->__toString();
            $this->data[$i]['lien'] = $value->link->__toString();
            // $this->data[$i]['id'] = $i;
            // image
            $content = $value->children('media', true)->content;
            $contentattr = $content->attributes();
            foreach ($contentattr as $name => $value) {
                // echo $name . ' = '.  $value . '<br />';
                $this->data[$i]['image'][$name] = $value->__toString();
            }
            $i++;
        }
        return $this->data;
    }

    public function SaveToDB()
    {
        $datas = null;
        try {
            $datas = self::getData();
            $status = true;
            $message = 'Mise à jour des informations avec succès !';
            foreach ($datas as $data) {
                $info = Info::where('lien', $data['lien'])->first();
                if (!$info) {
                    $date_for_human =  Carbon::parse($data['date_pub'])->diffForHumans();
                    
                    Info::create([
                        'titre' => $data['titre'],
                        'description' => $data['description'],
                        'lien' => $data['lien'],
                        'date_pub' => Carbon::parse('Fri, 22 Apr 2022 10:32:13 +0200')->format('D, d F Y h:i'),
                        'image' => $data['image']['url'],
                        'date_for_human' => $date_for_human
                    ]);
                }
            }
        } catch (\Throwable $th) {
            $status = false;
            $message = 'Erreur de mise à jour d\'informations !'.$th->getMessage();
        }
        return response()->json([
            'data' => Info::orderBy('date_pub','desc')->paginate(10),
            'message' => $message,
            'status' => $status
        ]);
    }

    public function getNews($page = 1, $perPage = 15)
    {
        $dataCurrent = Info::orderBy('date_pub','desc')->get();
        return $dataCurrent;
    }
    public function getInfo(String $titre)
    {
        $info = '';
        $info = Info::where('titre',$titre)->first();
        return response()->json([
            'data' => $info
        ]);
    }
    public function setInfo(Request $req)
    {
        $data = $req->all()[0];
        $info = Info::find($data['id']);
        $info->titre = $data['titre'];
        $info->description = $data['description'];
        $info->save();
        return response()->json([
            'data' => $info,
            'message' => 'Modification avec succès'
        ]);
    }
}
