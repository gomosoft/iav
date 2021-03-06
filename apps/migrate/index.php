<?

require_once($_SERVER["DOCUMENT_ROOT"] . "/apps/config.inc.php");
require_once(dirname(__FILE__) . "/libs/db.controller.php");


class migrateException extends Exception{}

class migrate{

	  protected $db;
	  protected $col;

	 function __construct(){

	 }

	 function run(){

	 	$this->db = new mysqlity;

    if(!isset($_GET["cod"]))
	 	$rs = $this->db->find();
    else
    {      

          $codigo = $this->db->compile( $_GET["cod"] . "{{d}}");
          $query = " WHERE codigo = ? LIMIT 1";
          $rs = $this->db->find(null, "inmueble", $query);

    }


	 	$this->doo($rs);

	 }


     public function num_pics($id){

       $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

       $rs = $db->query("SELECT id FROM `fotos_inm` WHERE cod_inm = {$id}");
       return $rs->num_rows;

   }

 
 public function fields_completed($data){
  
  $fields = 0;

   foreach($data as $field)
    if(!empty($field) AND $field != NULL)
      if(is_numeric($field) )
        if($field > 0)
          $fields++;
        else
          $field++;

    return $fields;

 }


 public function getDpto($ciudad){

         $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

       $rs = $db->query("SELECT departamento_iddepartamento AS id FROM `municipio` WHERE idmunicipio = {$ciudad} LIMIT 1");
       $rs = $rs->fetch_assoc();
       return $rs["id"];

 }



	 protected function doo($rs){


	 	$con = new MongoClient();
	 	$to = $con->selectDB("iav2");
	 	$this->col = $to->inmuebles;	 	

	 	foreach ($rs as $doc){

   // $doc["codigo"] = (int) $doc["codigo"] ;
	 	  
	 	      $id = $this->db->compile($doc["codigo"] . "{{d}}");
  	  	  $query = " WHERE cod_inm = ? LIMIT 1";
  	  	  $rss = $this->db->find(null, "fotos_inm", $query);


  	  	  $doc["campo_4"] = (int) $doc["campo_4"];
  	  	  $doc["campo_53"] = (int) $doc["campo_53"] ;          
  	  	  $doc["campo_5"] = (int) $doc["campo_5"];
  	  	  $doc["campo_17"] = (int) $doc["campo_17"];
  	  	  $doc["campo_9"] = (int) $doc["campo_9"];
  	  	  $doc["campo_24"] = (int) $doc["campo_24"];
          $doc["campo_6"] = (int) $doc["campo_6"];
  	  	  $doc["numvisitas"] = (int) $doc["numvisitas"];    
          $doc["fields_completed"] = (int) $this->fields_completed($doc);
          $doc["num_pics"] = (int) $this->num_pics($doc["codigo"]);
          $doc["description_length"] = (int) $doc["description_length"];
          $doc["dpto"] = (int) $this->getDpto((int) $doc["ciudad"]);

         // $doc["campo_53"] = ($doc["campo_53"] < 0) ? ($doc["campo_53"] * -1) : $doc["campo_53"];
          // $doc["campo_5"] = ($doc["campo_5"] < 0) ? ($doc["campo_5"] * -1) : $doc["campo_5"];



  	  	  if(isset($rss[0])){
  	  	  $rss = $rss[0];
  	  	  $doc["image"] = $rss["foto"];
  	  	   }

  	  	   $city = (array) json_decode(file_get_contents("http://inmueblealaventa.com/city/{$doc["ciudad"]}&json"), true);
  	  	   $doc["city"] = $city["rs"]["name"];

  	  	   $short = (array) json_decode(file_get_contents("http://inmueblealaventa.com/short/get/{$doc["codigo"]}&json"), true);
  	  	   $doc["short"] = $short["rs"]["short"];


  	  	  $id = $this->db->compile( $doc["usuario"] . "{{d}}");
  	  	  $query = " WHERE identificacion = ? LIMIT 1";
  	  	  $rss = $this->db->find(null, "usuarios", $query);

  	  	  if(isset($rss[0]))
  	  	  {
  	  	  	$rss = $rss[0];
  	  	  if($rss["banner1"] != NULL) 
  	  	  	$doc["pic_inmo"] = $rss["banner1"];
  	  	  else if($rss["banner2"] != NULL) 
  	  	  	$doc["pic_inmo"] = $rss["banner2"];

  	  	  }

     foreach($doc as $key => $val)
        if(empty($doc[$key]))
            unset($doc[$key]);

  	  

	 	  $this->col->insert($doc);	

	 	   }

	 	 $this->done(); 	 	

	 }


	 protected function done(){
	 	echo json_encode(array("success" => "yes"));
	 	die;	 	
	 }

}



try{

	$app = new migrate();
	$app->run();

}catch(migrateException $e){
	echo json_encode(array("error" => $e->getMessage()));
	die;
}