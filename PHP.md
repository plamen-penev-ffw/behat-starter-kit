# PHP Checkup & Installation
> In order to Behat to work we need PHP installed. The required version of PHP is 7 and above. Preferably version 7.3

> **NOTE** Mac users can skip **Step 1** because MacOS comes with preinstalled PHP that is compatible with the Behat requriements. You only need to check and install php extensions by reading **Step 2**.

# PHP Version and availability
> Check if you have PHP installation and if there is one - what version it is. To do that, you need to run this command in the terminal:

> **NOTE:** Windows users need to login to WSL in order to execute terminal commands. Click [**here**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/WSL.md#opening-linux-terminal) to see how.
```
php -v
```
If you see this in the terminal:
```
PHP 7.3.20-1+ubuntu16.04.1+deb.sury.org+1 (cli) (built: Jul  9 2020 16:33:48) ( NTS )
```
it means you have PHP version 7.3 installed and there is no need to install PHP. If the version is greater than 7.3 that is OK. You can proceed with checking the extensions for PHP by reading **Step 2** of this tutorial.  
If the version is bellow 7.3 proceed with **Step 1** of this tutorial to upgrade PHP to the needed version. 

On the other hand, if you see this in the terminal:
```
The program 'php' can be found in the following packages:
```
it means PHP is not installed and you need to proceed with **Step 1** of this tutorial.

# Step 1 - Installing or upgrading PHP
> The process of installing and upgrading the PHP is pretty the same. 

First you need to update your Linux instalation. To do that open an terminal and type the following commands:
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
sudo apt-get install php7.3 php7.3-cli php7.3-common
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
> **NOTE:** Please make sure that the correct version is specified. The examples below are for PHP 7.3.
## Windows users on WSL
- Install bz2
```
sudo apt-get install php7.3-bz2
```
- Install curl
```
sudo apt-get install php7.3-curl
```
- Install mbstring
```
sudo apt-get install php7.3-mbstring
```
- Install bcmath
```
sudo apt-get install php7.3-bcmath
```
- Install json
```
sudo apt-get install php7.3-json
```
- Install zip
```
sudo apt-get install php7.3-zip
```
- Install dom
```
sudo apt-get install php7.3-dom
```
## Mac users
- Install bz2
```
pecl install php7.3-bz2
```
- Install curl
```
pecl install php7.3-curl
```
- Install mbstring
```
pecl install php7.3-mbstring
```
- Install bcmath
```
pecl install php7.3-bcmath
```
- Install json
```
pecl install php7.3-json
```
- Install zip
```
pecl install php7.3-zip
```
- Install dom
```
pecl install php7.3-dom
```
