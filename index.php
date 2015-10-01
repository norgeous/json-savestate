<?php

// possible return results:
// empty,isnull,invalid,exists,stored

$savefolder = "saves";

$returnmessage = new stdClass();
$returnmessage->result = "default";
$returnmessage->errors = array();


// check query is not empty
if(empty($_GET['query'])) {

	$returnmessage->result = "empty";
	array_push($returnmessage->errors, "query is empty");

} else {

	//convert json string to json object
	$data = json_decode(urldecode($_GET['query']));
	if(is_null($data)) {

		$returnmessage->result = "isnull";
		array_push($returnmessage->errors, "JSON is null");

	}else{

		// load json-schema for PHP (https://github.com/justinrainbow/json-schema)
		require __DIR__ . '/vendor/autoload.php';
		$retriever = new JsonSchema\Uri\UriRetriever;
		$schema = $retriever->retrieve('file://' . realpath('schema.json'));
		$refResolver = new JsonSchema\RefResolver($retriever);
		$refResolver->resolve($schema, 'file://' . __DIR__);
		$validator = new JsonSchema\Validator();
		$validator->check($data, $schema);

		// validate against schema
		if (!$validator->isValid()) {

			$returnmessage->result = "invalid";
			array_push($returnmessage->errors, "JSON does not validate to schema");
			foreach ($validator->getErrors() as $error) {
				array_push($returnmessage->errors, "schema error: ".$error['property'].$error['message']);
			}

		} else {

			// restringify the json object and get crc of the string
			$jsonstring = json_encode($data);
			$hash = dechex(crc32($jsonstring));
			$filename = $savefolder.'/'.$hash.'.json';
			$file = './'.$filename;

			// check file already exists
			if(file_exists($file)){

				$returnmessage->result = "exists";
				$returnmessage->path = $filename;
				array_push($returnmessage->errors, "JSON file already exists");

			} else {

				$returnmessage->result = "stored";
				$returnmessage->path = $filename;
				file_put_contents($file, $jsonstring);
				
			}
		}
	}
}
if($returnmessage->result === "empty"){
	echo '<a href="?query='.urlencode(preg_replace('/\s+/', '', file_get_contents('test.json'))).'">test.json</a>';
} else{
	
header('Content-Type: application/json');
echo json_encode($returnmessage);

}
?>