<?php
header("Content-Type: application/json");
$dataFile = "data.json";
if (!file_exists($dataFile)) file_put_contents($dataFile, json_encode([]));
function readData($f){ return json_decode(file_get_contents($f),true); }
function saveData($f,$d){ file_put_contents($f,json_encode($d,JSON_PRETTY_PRINT)); }
$method=$_SERVER["REQUEST_METHOD"];
$action=$_GET["action"]??"";

if($method==="POST" && $action==="create"){
 $in=$_POST;
 $id=$in["id"]??""; $name=$in["name"]??""; $phone=$in["phone"]??"";
 if(!$id) die(json_encode(["error"=>"ID is required"]));
 if(!$name) die(json_encode(["error"=>"Name cannot be empty"]));
 if(strlen($phone)<10) die(json_encode(["error"=>"Phone must be at least 10 digits"]));
 $data=readData($dataFile);
 foreach($data as $d){ if($d["id"]==$id) die(json_encode(["error"=>"ID exists"])); }
 $data[]=["id"=>$id,"name"=>$name,"phone"=>$phone];
 saveData($dataFile,$data);
 die(json_encode(["success"=>true]));
}

if($method==="POST" && $action==="update"){
 $id=$_POST["id"]??""; if(!$id) die(json_encode(["error"=>"ID needed"]));
 $name=$_POST["name"]??null; $phone=$_POST["phone"]??null;
 $data=readData($dataFile); $found=false;
 foreach($data as &$d){
  if($d["id"]==$id){
   $found=true;
   if($name!==null){ if(!$name) die(json_encode(["error"=>"Name empty"])); $d["name"]=$name; }
   if($phone!==null){ if(strlen($phone)<10) die(json_encode(["error"=>"Phone <10"])); $d["phone"]=$phone; }
  }
 }
 if(!$found) die(json_encode(["error"=>"ID not found"]));
 saveData($dataFile,$data);
 die(json_encode(["success"=>true]));
}

if($method==="POST" && $action==="delete"){
 $id=$_POST["id"]??""; if(!$id) die(json_encode(["error"=>"ID needed"]));
 $data=readData($dataFile); $new=[]; $found=false;
 foreach($data as $d){ if($d["id"]==$id){$found=true;continue;} $new[]=$d; }
 if(!$found) die(json_encode(["error"=>"ID not found"]));
 saveData($dataFile,$new);
 die(json_encode(["success"=>true]));
}

if($method==="POST" && $action==="upload-image"){
 if(!isset($_FILES["image"])) die(json_encode(["error"=>"Image required"]));
 if(!file_exists("uploads")) mkdir("uploads");
 $file="uploads/".time()."_".basename($_FILES["image"]["name"]);
 move_uploaded_file($_FILES["image"]["tmp_name"],$file);
 die(json_encode(["success"=>true,"file"=>$file]));
}

echo json_encode(["error"=>"Invalid route"]);
?>