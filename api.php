<?php // Set the private API key for the user (from the user account page) and the user we're accessing the system as.
$private_key="1b802d76e2eef4f0d49ad319448b0d32885870c5ab1f622e9404de9b282eb007";
$user="nwilson..LDAP";

// Search term. EDIT THIS AS NEEDED
$search=$argv[1];//"zabin";//"date:2014";

$stringsearch=str_replace(":","",$search);
$stringsearch=str_replace(" ","_",$stringsearch);
$stringsearch=str_replace("/","",$stringsearch);
$stringsearch=str_replace("\\","",$stringsearch);
$stringsearch=str_replace(",","",$stringsearch);
$stringsearch=str_replace(";","",$stringsearch);
$stringsearch=str_replace(".","",$stringsearch);
$start=1;
$end=0;
if(count($argv)>1){
	$start=$argv[2];
}
if(count($argv)>2){
	$end=(int) $argv[3];
}
$query="user=" . $user . "&function=do_search&param1=".$search."&param3=resourceid";

// Sign the query using the private key
$sign=hash("sha256",$private_key . $query);
// Make request for search results.
$response=json_decode(file_get_contents("http://resourcespace.ads.carleton.edu/api/?" . $query . "&sign=" . $sign));
$query="user=" . $user . "&function=get_resource_path&param1=" . $response[0]->ref . "&param2=False&param3=&param4=False&param5=" . $response[0]->file_extension;
$sign=hash("sha256",$private_key . $query);

echo(count($response));
echo("\n");
$i=1;

$meta_metadata=Array();

if(count($response)>0 && $start<=1){

	//Pull short and long metadata from the first item for headers
	$metacsv=fopen($stringsearch."_metadata.csv","w");
	$query="user=" . $user . "&function=get_resource_field_data&param1=" . $response[0]->ref;
	$sign=hash("sha256",$private_key . $query);
	$metadatafields=json_decode(file_get_contents("http://resourcespace.ads.carleton.edu/api/?" . $query . "&sign=" . $sign));
    $query="user=" . $user . "&function=get_resource_data&param1=" . $response[0]->ref;
	$sign=hash("sha256",$private_key . $query);
    $shortmetadatafields=json_decode(file_get_contents("http://resourcespace.ads.carleton.edu/api/?" . $query . "&sign=" . $sign));
    
    //Grab all the short metadata fields
    foreach($shortmetadatafields as $key=>$value){
    	if(strpos($key,'field')=== false){
			$meta_metadata[$key]=Array();
		}
    }
    
    //Grab all the long metadata fields
	foreach($metadatafields as $field){
		$meta_metadata[$field->title]=Array();
	}

	//Write the 1st line header.
	foreach($meta_metadata as $writedata=>$value){
		fwrite($metacsv,$writedata.",");
	}
	fwrite($metacsv,"\n");
}
else if($start>1){
	$metacsv=fopen($stringsearch."_metadatatemp.csv","a");
}

foreach($response as $resource){
	if($i<$start){
	    $i++;
		continue;
	}
	else if($i>$end && $end!=0){
	    $i++;
		continue;
	}
	echo("$i");
	echo(") id: ".$resource->ref);
	//Get resource file path
	$query="user=" . $user . "&function=get_resource_path&param1=" . $resource->ref . "&param2=False&param3=&param4=False&param5=" . $resource->file_extension;
	$sign=hash("sha256",$private_key . $query);
	$filepath=file_get_contents("http://resourcespace.ads.carleton.edu/api/?" . $query . "&sign=" . $sign);

	//Get filepath for metadata
    $writefile=str_replace("https:\/\/resourcespace.ads.carleton.edu",".",ltrim(rtrim($filepath,"\""),"\""));
    $writefile=str_replace("\/","/",$writefile);
    $filepath=str_replace("\/","/",ltrim(rtrim($filepath,"\""),"\""));
    $filepath=str_replace("https://","http://",$filepath);


    //Long form.
	$query="user=" . $user . "&function=get_resource_field_data&param1=" . $resource->ref;
	$sign=hash("sha256",$private_key . $query);
    $metadatafields=json_decode(file_get_contents("http://resourcespace.ads.carleton.edu/api/?" . $query . "&sign=" . $sign));
    //Short form.
    $query="user=" . $user . "&function=get_resource_data&param1=" . $resource->ref;
	$sign=hash("sha256",$private_key . $query);
    $shortmetadatafields=json_decode(file_get_contents("http://resourcespace.ads.carleton.edu/api/?" . $query . "&sign=" . $sign));
    $ref=strval($shortmetadatafields->ref);
    //Re-pair metadata fields with proper values
    foreach($shortmetadatafields as $key=>$value){
    	//echo($value);
    	//echo("\n");
    	$field_name=$key;
		if(strpos($key,'field')!== false){
			$field=substr($key,5);
			foreach($metadatafields as $test){
				if(strcasecmp($test->fref,$field)==0){
					$field_name=$test->title;
				}
			}
		}
		$meta_metadata[$field_name][$ref]=$value;
    }
    $OutputFilename=basename($writefile);
    if(array_key_exists($ref,$meta_metadata["Original filename"])){
    	$OutputFilename=$meta_metadata["Original filename"][$ref];
    }
    
    $Date="Undated";
    if(array_key_exists($ref,$meta_metadata["Date"])){
    	$Date=$meta_metadata["Date"][$ref];
    	$Date=substr($Date,0,10);
    	$Date=str_replace("-","",$Date);
    }
    $writefile="./".$stringsearch."/".$Date."/".$OutputFilename;
    
    
	echo(" filename: ".$writefile);

	echo(" URL: ".$filepath."\n");

	//Download resource
	if(!file_exists(dirname($writefile))){
		mkdir(dirname($writefile),0777,TRUE);
	}
	copy($filepath,$writefile);
	
//Write to file
	foreach($meta_metadata as $writedata){

		if(array_key_exists($ref,$writedata)){
			fwrite($metacsv,$writedata[$ref]);
		}
		fwrite($metacsv,",");
	}
	fwrite($metacsv,"\n");
	
    $i++;

}
fclose($metacsv);

?>
