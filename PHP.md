# PHP Checkup & Installation
> In order to Behat to work we need PHP installed. The required version of PHP is 7 and above. Preferably version 7.2

# PHP Version and availability
> Check if you have PHP installation and if there is one - what version it is. To do that, you need to run this command in the terminal:
> **NOTE:** Windows users need to login to their WSL to execute terminal commands.
```
php -v
```
If you see this in the terminal:
```
PHP 7.2.24-0ubuntu0.18.04.4 (cli) (built: Apr  8 2020 15:45:57) ( NTS )
```
it means you have PHP version 7.2 installed and there is no need to install PHP. If the version is greater than 7.2 that is OK. You can proceed with checking the extensions for PHP by reading **Step 2** of this tutorial.  
If the version is bellow 7.2 proceed with **Step 1** of this tutorial to upgrade PHP to the needed version. 

In other hand, if you see this in the terminal:
```
The program 'php' can be found in the following packages:
```
it means PHP is not installed and you need to proceed with **Step 1** of this tutorial.

# Step 1 - Installing or upgrading PHP
> The process of installing and upgrading the PHP is pretty the same. The difference is that when the installation of the new version is completed the old PHP installation needs to be disabled and the new to be enabled, this will be described in the installation process. 

First you need to update your current instalation. To do that open an terminal and type the following commands:
```
sudo apt-get update
```
```
sudo apt-get upgrade
```
Wait the process to finish and add the PHP repository by using:
```
sudo add-apt-repository -y ppa:ondrej/php
```
```
sudo apt-get update
```
Wait the process to finish and install PHP by using:
```
sudo apt-get install php7.2 php7.2-cli php7.2-common
```
Perform this step ONLY if you are upgrading your PHP version. Replace the number of the version after the "php" to the version of your old PHP: 
```
a2dismod php7.0
```
Enable the new PHP version.
```
a2enmod php7.2
```

# Step 2 - Check and install needed PHP extensions.
> Behat also needs the following PHP extensions to run:

- bz2
- curl
- mbstring
- bcmath
- json
- zip
- dom

To check what extension you currently have, type the folowing command in the terminal:
```
php -m
```
Then you will get a list of extensions and check if the abovementioned extensions is in the list. If some is missing you can install it as folows:
- Install bz2
```
sudo apt-get install php7.2-bz2
```
- Install curl
```
sudo apt-get install php7.2-curl
```
- Install mbstring
```
sudo apt-get install php7.2-mbstring
```
- Install bcmath
```
sudo apt-get install php7.2-bcmath
```
- Install json
```
sudo apt-get install php7.2-json
```
- Install zip
```
sudo apt-get install php7.2-zip
```
- Install dom
```
sudo apt-get install php7.2-dom
```
