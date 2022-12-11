<?php
require_once('simpleDOM/simple_html_dom.php');

$baseUrl = 'https://www.chileautos.cl/';

$html = getHtmlFromUrl('https://www.chileautos.cl/vehiculos/autos-veh%C3%ADculo/chevrolet/');


$html_cards = $html->find('.listing-item.card');

$car =[];

//iteramos cards
foreach($html_cards as $htmlCardHeader) {

    //buscamos dentro de la card
    $cardHeaderlinks = $htmlCardHeader->find('a');
    foreach($cardHeaderlinks as $link) {
      //echo $link->href . '<br>';
      //echo "<br>";
      $car['link']= $link->href;
    }

    $dummyLink = "https://www.chileautos.cl/vehiculos/detalles/2017-chevrolet-d-max-4wd-2-5/CP-AD-8004647/?Cr=0&gts=CP-AD-8004647&gtsSaleId=CP-AD-8004647&gtsViewType=topspot&rankingType=topspot";

//    getCardDataFromDetailUrl($baseUrl.$car['link'],$car);
    getCardDataFromDetailUrl($dummyLink,$car);

    print_r($car);
    exit(1);
}





function getCardDataFromDetailUrl($carUrl,&$car ){

  $htmlDetail = getHtmlFromUrl($carUrl);

  $htmlContainer = $htmlDetail->find('.container');
  foreach($htmlContainer as $container) {
   
    //titulo
    $h1Titulo = $container->find('h1');
    foreach($h1Titulo as $h1) {
      $car['title']= $h1->plaintext;
    }

    //km
    $divsKm = $container->find('.key-details-item-image.key-details-item-type-odometer');
    foreach($divsKm as $div) {
      $car['mileage']= $div->next_sibling()->firstChild()->plaintext;
      //$car['mileage']= $div->next_sibling;
    }

    //transmision
    $divsTransmision = $container->find('.key-details-item-image.key-details-item-type-transmission');
    foreach($divsTransmision as $div) {
      $car['transmision']= $div->next_sibling()->firstChild()->plaintext;
      //$car['mileage']= $div->next_sibling;
    }


    //fuelType
    $divsFueltype = $container->find('.key-details-item-image.key-details-item-type-fueltype');
    foreach($divsFueltype as $div) {
      $car['fuelType']= $div->next_sibling()->firstChild()->plaintext;
      //$car['mileage']= $div->next_sibling;
    }
    

    //comments
    $divscomments = $container->find('.view-more-target');
    foreach($divscomments as $div) {
      $car['comments']= $div->firstChild()->plaintext;
      //$car['mileage']= $div->next_sibling;
    }


    //images
    $divsImages = $container->find('.col-2.gallery-thumbnails');
    $re = '/(\((?>[^()]+|(?1))*\))/';
    foreach($divsImages as $divGallery) {
      $divsThumbs = $divGallery->find('.thumb-small');
      foreach($divsThumbs as $thumb) {
        $styleThumb = $thumb->style;
        preg_match_all($re, $styleThumb, $matches);

        $car['thumbs'][]=$matches[0];  
      }
    }


    //region - comuna - precio
    // id sections-contents
    $divSectionContents = $container->find('div[id=sections-contents]');

    foreach($divSectionContents as $sectionContents) {
      $divRegion = $sectionContents->find('.row.features-item.features-item-regin');
      foreach($divRegion as $div) {
        $car['region']= $div->firstChild()->next_sibling()->plaintext;
        $car['comuna']= $div->next_sibling()->firstChild()->next_sibling()->plaintext;
      }

      $divRegion = $sectionContents->find('.row.features-item.features-item-precio');
      foreach($divRegion as $div) {
        $car['price']= $div->firstChild()->next_sibling()->plaintext;
      }

    }


  }


}


function getHtmlFromUrl($url){
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    //CURLOPT_URL => 'https://www.chileautos.cl/vehiculos/autos-veh%C3%ADculo/chevrolet/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'accept-language:  en-US,en;q=0.9,es;q=0.8',
      'user-agent:  Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
      'Cookie: csncidcf=FF780FE5-6AF3-49D8-AA24-D15A72FB223B; datadome=7cGHp-Yi0aKVk0e7lF8SPF0hL_dotPSr5w~BHpsV~xnXSV3J7ZIF3EiAlSnfiHZEioQC~mzgDE2~Loak~cDRYSHp2c1Mp~JdtSxe4Gk0qrMESi6UzOMB_BEnoD2~oznQ; cidgenerated=1; csnclientid=91BEF030-C44A-4E32-94AA-6137BE31D098'
    ),
  ));
  $response = curl_exec($curl);
  return str_get_html($response);
}