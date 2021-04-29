# Amazon s3 to Akeneo Growth Edition and Community Edition
Automatically sync images and files from an S3 bucket into an Akeneo PIM. Both the SKU and attribute are detected via file naming convention. This is a completely SaaS compliant tool.

### Note: This free and open-source POC is not supported. Please work with your system integrator to ensure proper implementation.

## Requirements
Either:
+ Akeneo Growth Edition
+ Akeneo Community Edition >=4.0


## The Basics
The application checks an Amazon S3 bucket for any files that have not been synced into Akeneo. For those that need to be synced, it will use a naming convention to assign thr assets to the right attribute in the right product.

The default naming convention is productid--attribute_name-anything_else.jpg/png/gif/pdf
So, given two assets with the following names will sync as such:
+ 123456--productimage-ecomm.jpg  -> Product 123456 in the productimage attribute.
+ 098765--manual-en_US.pdf        -> Product 098765 in the manual attribute.

## Setting it up
 1. Supply your PIM credentials in the .env file.
 2. Supply your Amazon S3 credential in the amazonakeneo.php file. (Lines 11-19.)


## Instructions
Execute the script by running `php amazonakeneo.php` from your bash terminal. This process can be put on an automated schedule.

## Notes
If needed, this can be extended to EE while utilizing the more powerful, enterprise grade Assets Manager or as standars image and file attribute as seen here. To use this on EE, you would just need to change the amazonakeneo.php file as follows:
+ Change 'use \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;' to 'use \Akeneo\Pim\ApiClient\AkeneoPimEnterpriseClientBuilder;' (Line 7.)
+ CHANGE '$clientBuilder = new AkeneoPimClientBuilder($__ENV['API_URL']);' to '$clientBuilder = new AkeneoPimEnterpriseClientBuilder($__ENV['API_URL']);' (Line 29.)

## To-do
Currently this is a limited POC and only supports simple products and product level image and file attributes in variant products. In the future, support for product model level attributes may be added.
