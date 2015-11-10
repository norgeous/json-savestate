<?php

$savefolder = "saves";
$schemafile = "schema.json";

$returnmessage = new stdClass();
$jsonpostbody = file_get_contents('php://input');

// check query is not empty
if(empty($jsonpostbody)) {

	$returnmessage->error = "POST body is empty";

} else {

	//convert json string to json object
	$data = json_decode($jsonpostbody);
	if(is_null($data)) {

		$returnmessage->error = "JSON does not decode";

	} else {

		// load json-schema for PHP (https://github.com/justinrainbow/json-schema)
		require __DIR__ . '/vendor/autoload.php';
		$retriever = new JsonSchema\Uri\UriRetriever;
		$schema = $retriever->retrieve('file://' . realpath($schemafile));
		$validator = new JsonSchema\Validator();
		$validator->check($data, $schema);

		// validate against schema
		if (!$validator->isValid()) {

			$returnmessage->error = "JSON does not validate to schema (serverside):";
			foreach ($validator->getErrors() as $error) {
				$returnmessage->error .= " ".$error['message'].';';
			}

		} else {

			// restringify the json object and get crc of the string
			$jsonstring = json_encode($data);
			$hash = dechex(crc32($jsonstring));
			$filename = $savefolder.'/'.$hash.'.json';
			$file = './'.$filename;

			// check file already exists and if not store
			if(!file_exists($file)){
				file_put_contents($file, $jsonstring);
			}

			$returnmessage->result = $hash;
		}
	}
}

header('Content-Type: application/json');
echo json_encode($returnmessage);

?>