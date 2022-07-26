<?php
/**
 * API para consulta de CNPJ e retorno dos valores públicos
 * Limite de Controle para Pesquisas de 3 consultas por MINUTO
 * Author: Vitor Hugo Marini
 * Projeto: https://github.com/cnpj-ws
 * Documentação: https://www.cnpj.ws/docs/api-publica/consultando-cnpj
 * Site: https://www.cnpj.ws/
 * Data Criação: 26/07/2022
 */

 #Exemplo de chamada
 #consultaSUFRAMA("xxxxxxxxxxxx", "YYYYYYYYY") // Consulta do registro no SUFRAMA
 #consultaDADOS("xxxxxxxxxxxx")                // Consulta dos dados empresariais pelo CNPJ

function consultaDADOS($xCNPJ){

 #Recebendo o valor 
 $valor = justNumber(strtolower($xCNPJ));

 if ( strlen($valor) == 14 ){
  $tipo = true;
 }else{
  $tipo = false;
 }

 #Tratando o valor
 if ( $tipo ){

  $url = "https://publica.cnpj.ws/cnpj/".$valor;

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  //for debug only!
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  $resp = curl_exec($curl);
  curl_close($curl);

  $ret = json_decode($resp);
  
  #Validando o limite de requisição
  if ( $ret->status == "429" ){
   $retorno = array(
    "status" => "ERRO",
    "data"   => "LIMITE DE CONSULTAS EXCEDIDO, MÁXIMO DE 3 CONSULTAS POR MINUTO. AGUARDE!"
   );     
  }else{
   $retorno = array(
    "status" => "SUCESSO",
    "data"   => json_decode($resp)
   );  
  }

 }else{
  $retorno = array(
   "status" => "ERRO",
   "data"   => "CONSULTA INVÁLIDA PARA  - ".$xCNPJ
  );   
 }

 return $retorno;

}

function consultaSUFRAMA($xCNPJ, $xIE){

 #Recebendo o valor 
 $valor1 = justNumber(strtolower($xCNPJ));
 $valor2 = justNumber(strtolower($xIE));

 #Verificando se os dados que serão consultados estão vigentes
 if ( strlen($valor1) == 14 && !empty($valor2) ){
  $tipo = true;
 }else{
  $tipo = false;
 }

 #Tratando o valor
 if ( $tipo ){

  #Inciando a chamada -Abrindo a conexão
  $curl = curl_init();

  #Alimentado os dados para que seja feito a requisição via POST
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://publica.cnpj.ws/suframa',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
      "cnpj":"61940292006682",
      "inscricao":"210140267"
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));

  #Armazena o retorno dos dados
  $retorno = curl_exec($curl);
  print"<pre>";
  print_r(curl_error($curl));
  exit;

  #Encerra a conexão
  curl_close($curl);

  // $retorno = json_decode($response);  

 }else{
  $retorno = "CONSULTA INVÁLIDA PARA  - ".$xCNPJ." e ".$xIE;
 }
 return  $retorno;
}

/**
 * @@ Retorna somente os números - tratamento extra
 *  - @ $string - Valor a ser tratado
 */
function justNumber($string) {

 #Opções de Entrada
 $in = array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','É','Í','Ó',
              'Ú','ñ','Ñ','ç','Ç',' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>','<','ª','º','.',',','-',
              'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','x','w','y','z' );

 #Retorno do valor
 return str_replace($in, "", $string);
}