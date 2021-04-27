<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Symfony\Component\Dotenv\Dotenv;

//Create the S3Client
$s3Client = new S3Client([
    'region' => 'us-west-1',
    'version' => 'latest',
    'credentials' => [
        'key' => 'YOURIAMKEY',
        'secret' => 'YOURIAMSECRET'
    ]
]);

// Get the API VARS are in the .env file
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
if (file_exists(__DIR__ . '/.env.local')) {
    $dotenv->load(__DIR__ . '/.env.local');
}

/** SET THE API CLIENT */
$clientBuilder = new AkeneoPimClientBuilder($_ENV['API_URL']);
$client = $clientBuilder->buildAuthenticatedByPassword(
    $_ENV['API_CLIENT'],
    $_ENV['API_SECRET'],
    $_ENV['API_USERNAME'],
    $_ENV['API_PASSWORD']
);

//List all images in bucket
//Adopt a naming convention of sku--attribute_code-anythingelse.jpg/png/gif
//Read all files in a bucket, break the file name into pieces to be used to assign the files to the right product and attribute.
$bucketname = 'akeneoaws';
$objectsListResponse = $s3Client->listObjects(['Bucket' => $bucketname]);
$objects = $objectsListResponse['Contents'] ?? [];
foreach ($objects as $object) {
    $arr = explode("--", $object['Key'], 2); //Break the file name apart
    $sku = $arr[0]; //Assign the first part of the file name to the sku variable
    $att = explode("-", $arr[1], 2); //Break the file name apart again and assign to the attribute variable

    // See if the image already exists in the SKU
    $searchBuilder = new SearchBuilder();
    $searchBuilder->addFilter('identifier', '=', $sku);
    $searchBuilder->addFilter( $att[0], 'EMPTY');
    $searchFilters = $searchBuilder->getFilters();
    $products = $client->getProductApi()->all(1, ['search' => $searchFilters]);
    foreach ($products as $product) {
        $file = $s3Client->getObject([
            'Bucket' => $bucketname,
            'Key' => $object['Key']
        ]);
        file_put_contents($object['Key'], $file['Body']->getContents());
        $productsku = $product['identifier'];
        $productname = $product['values']['name'][0]['data'];
        $client->getProductMediaFileApi()->create($object['Key'], [
            'identifier' => $productsku,
            'attribute' => $att[0],
            'scope' => null,
            'locale' => null
        ]);
        echo "[AWS to Akeneo Growth Edition Connector] - " .
            $object['Key'] .
            " has been synced to " .
            $productname .
            " on '" .
            $_ENV['API_URL'] .
            "'.\n";
        unlink($object['Key']);
    }
}
