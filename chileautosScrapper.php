<?php
require_once ('simpleDOM/simple_html_dom.php');
require_once ('MySQLConnect.php');

use Database\MySQLConnect as Mysql;



$baseUrl = 'https://www.chileautos.cl/';

$html = getHtmlFromUrl('https://www.chileautos.cl/vehiculos/autos-veh%C3%ADculo/chevrolet/');

$html_cards = $html->find('.listing-item.card');

$car = [];

//iteramos cards
$i=0;
foreach ($html_cards as $htmlCardHeader)
{
    $i++;

    //buscamos dentro de la card
    $cardHeaderlinks = $htmlCardHeader->find('a');
    foreach ($cardHeaderlinks as $link)
    {
        $car['link'] = $link->href;
    }

    $dummyLink = "https://www.chileautos.cl/vehiculos/detalles/2021-chevrolet-captiva-1-5-premier-cvt/CP-AD-8142455/?Cr=0&gts=CP-AD-8142455&gtsSaleId=CP-AD-8142455&gtsViewType=topspot&rankingType=topspot";

    //    getCardDataFromDetailUrl($baseUrl.$car['link'],$car);
    getCardDataFromDetailUrl($dummyLink, $car);

    getBrandId($car);
    saveVehicle($car);

    // print_r($car);
    // exit(1);

    if($i>20){
        break;
    }
}

function getCardDataFromDetailUrl($carUrl, &$car)
{

    $htmlDetail = getHtmlFromUrl($carUrl);

    $htmlContainer = $htmlDetail->find('.container');
    foreach ($htmlContainer as $container)
    {

        //titulo
        $h1Titulo = $container->find('h1');
        foreach ($h1Titulo as $h1)
        {
            $car['title'] = trim($h1->plaintext);
            $car['year'] = substr(trim($h1->plaintext) , 0, 4);
        }

        //km
        $divsKm = $container->find('.key-details-item-image.key-details-item-type-odometer');
        foreach ($divsKm as $div)
        {
            $car['mileage'] = $div->next_sibling()
                ->firstChild()->plaintext;
        }

        //transmision
        $divsTransmision = $container->find('.key-details-item-image.key-details-item-type-transmission');
        foreach ($divsTransmision as $div)
        {
            $car['transmision'] = $div->next_sibling()
                ->firstChild()->plaintext;
        }

        //fuelType
        $divsFueltype = $container->find('.key-details-item-image.key-details-item-type-fueltype');
        foreach ($divsFueltype as $div)
        {
            $car['fuelType'] = $div->next_sibling()
                ->firstChild()->plaintext;
        }

        //comments
        $divscomments = $container->find('.view-more-target');
        foreach ($divscomments as $div)
        {
            $car['comments'] = $div->firstChild()->plaintext;

        }

        //images
        $divsImages = $container->find('.col-2.gallery-thumbnails');
        $re = '/(\((?>[^()]+|(?1))*\))/';
        foreach ($divsImages as $divGallery)
        {
            $divsThumbs = $divGallery->find('.thumb-small');
            foreach ($divsThumbs as $thumb)
            {
                $styleThumb = $thumb->style;
                preg_match_all($re, $styleThumb, $matches);

                $car['thumbs'][] = $matches[0];
            }
        }

        //region - comuna -
        $divSectionContents = $container->find('div[id=sections-contents]');

        foreach ($divSectionContents as $sectionContents)
        {
            $divRegion = $sectionContents->find('.row.features-item.features-item-regin');
            foreach ($divRegion as $div)
            {
                $car['region'] = trim($div->firstChild()
                    ->next_sibling()->plaintext);
                $car['comuna'] = trim($div->next_sibling()
                    ->firstChild()
                    ->next_sibling()->plaintext);
            }

            //precio
            $divPrice = $sectionContents->find('.row.features-item.features-item-precio');
            foreach ($divPrice as $div)
            {
                $car['price'] = trim($div->firstChild()
                    ->next_sibling()->plaintext);
            }

            //type
            // specifications-version 
            //multi-collapse collapse show
            $divDetails = $sectionContents->find('div[id=specifications-detalles]');
            foreach ($divDetails as $detail)
            {
                $divCategory = $detail->find('.col.features-item-value.features-item-value-tipo-categoria');
                foreach ($divCategory as $div)
                {
                    $car['type'] = trim($div->plaintext);
                }

                //model
                $divModel = $detail->find('.col.features-item-value.features-item-value-modelo');
                foreach ($divModel as $div)
                {
                    $car['model'] = trim($div->plaintext);
                }

                //brand
                $divBrand = $detail->find('.col.features-item-value.features-item-value-marca');
                foreach ($divBrand as $div)
                {
                    $car['brand'] = trim($div->plaintext);
                }
            }


            $divDetails = $sectionContents->find('div[id=specifications-version]');
            foreach ($divDetails as $detail)
            {
                $divCategory = $detail->find('.col.features-item-value.features-item-value-tipo-categoria');
                foreach ($divCategory as $div)
                {
                    $car['type'] = trim($div->plaintext);
                }

                //model
                $divModel = $detail->find('.col.features-item-value.features-item-value-modelo');
                foreach ($divModel as $div)
                {
                    $car['model'] = trim($div->plaintext);
                }

                //brand
                $divBrand = $detail->find('.col.features-item-value.features-item-value-marca');
                foreach ($divBrand as $div)
                {
                    $car['brand'] = trim($div->plaintext);
                }
            }


        }

    }

}

function getHtmlFromUrl($url)
{
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
        ) ,
    ));
    $response = curl_exec($curl);
    return str_get_html($response);
}



function saveVehicle($car){

  
  $today = date("yyyy-mm-dd"); 



  $idBrand = getBrandId($car);


  $sqlVehicle = 
  "INSERT INTO `vehiculo` 
    (`anio`, 
    `combustible`, 
    `comentario`,
    `created_at`, 
    `kilometraje`,  
    `precio`,
    `tipo`, 
    `transmision`,
    `updated_at`,
    `url`,
    `ciudad_id`, 
    `marca_id`,
    `tipo_vehiculo_id`, 
    `model`,
    `provider_id`) 
    VALUES
  (". $car['year'] ."
  ,". $car['fuelType'] ."
  ,". $car['comments'] ."
   ,  ".$today.",
    ". $car['mileage'] ."
    ,". $car['price'] ."
    ,". $car['type'] ."
      ,". $car['type'] ."
      ,". $car['transmision'] ."
      ,". $car['link'] ."
      NULL
      , ".$idBrand."
      , NULL
      , 18490000
      , NULL
      , NULL
      , 'Automático', '2022-07-07', 'renault-arkana-2021_83736235', ". $car['model'] .", 15, 9, 2, 'ARKANA', 1);";


  if ($mysql->query($sqlVehicle) === TRUE) {
    echo "New record created successfully";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }


/*
INSERT INTO `vehiculo` (`id`, `anio`, `cilindros`, `color`, `combustible`, `comentario`, `consumo`, `created_at`, `detalle`, `estado`, `id_yapo`, `kilometraje`, `litros_motor`, `mail`, `patente`, `precio`, `telefono`, `tipo`, `transmision`, `updated_at`, `url`, `vendedor`, `ciudad_id`, `marca_id`, `tipo_vehiculo_id`, `model`, `provider_id`) VALUES
(111111111, '2021', NULL, NULL, 'Bencina', 'VALOR PUBLICADO INCLUYE BONO FINANCIAMIENTO SANTANDER, VALOR CONTADO 19.490.000\n\nIvan Mascayano: +569 83964087 \nMonica Gutierrez: +569 78951910 Iberocar.cl - Único en su estado- Real Oportunidad en Seminuevos - Recibimos tu auto en parte de pago - Financiamiento con Santander- Cancela con Tarjetas de crédito o transferencia- Entrega inmediata- Descuentos y Bonos Exclusivos- Calidad y Seguridad. Vendemos marcas como: Jeep, Chevrolet, Chery, Volkswagen, Kia, Hyundai, Suzuki, Toyota, Peugeot , Nissan, Ford, Mazda, MG y más', NULL, '2022-06-08', NULL, NULL, '83736235', 32000, NULL, NULL, NULL, 18490000, NULL, NULL, 'Automático', '2022-07-07', 'renault-arkana-2021_83736235', 'iberocar nos', 15, 9, 2, 'ARKANA', 1);
*/
}

function getBrandId($car){
    $mysql = new Mysql('localhost','root','2544634','soloautos');

    $mysql->connect();


    $sqlBrand = "SELECT * FROM `marca` where marca.nombre_marca = '". strtolower($car['brand'])."';";
    
    $idBrand = 0;
    if ($result = $mysql->query($sqlBrand)) {
        
        if($result->num_rows>0){
            while ($row = mysqli_fetch_row($result)) {
                 $idBrand = $row[0];
               }
               
        }else{
            $sqlBrand = "INSERT INTO `marca` (`nombre_marca`) 
            VALUES('". strtolower($car['brand'])."')";
    
            if ($mysql->query($sqlBrand) === TRUE) {
                $idBrand = $mysql->getLastId();
               // echo "brand ".$idBrand." was created successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        mysqli_free_result($result);
    }

    return $idBrand;
}


function getCity($car){


    $mysql = new Mysql('localhost','root','2544634','soloautos');

    $mysql->connect();

    $sqlCity = "SELECT * FROM `ciudad` WHERE lower(ciudad.comuna_nombre) = 'temuco';";
    
    $idCity = 0;
    if ($result = $mysql->query($sqlCity)) {
        
        if($result->num_rows>0){
            while ($row = mysqli_fetch_row($result)) {
                 $idCity = $row[0];
               }
               
        }else{
            $sqlCitySave = "INSERT INTO `marca` (`nombre_marca`) 
            VALUES('". strtolower($car['brand'])."')";
    
            if ($mysql->query($sqlBrand) === TRUE) {
                $idBrand = $mysql->getLastId();
               // echo "brand ".$idBrand." was created successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        mysqli_free_result($result);
    }

    return $idCity;

}

function saveImages($car){

}


// falta hacer la relacion con ciudad y region