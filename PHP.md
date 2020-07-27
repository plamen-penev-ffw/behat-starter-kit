# PHP Checkup & Installation
> In order to Behat to work we need PHP installed. The required version of PHP is 7 and above. Preferably 7.2.

**The following PHP extensions are needed too:**

- bz2
- curl
- mbstring
- bcmath
- json
- zip
- fileinfo

# PHP Version and availability checkup
> Check if you have PHP installed and what version it is. To do that, you need to run this command in the terminal.
```
php -v
```
If you see this in the terminal
```
PHP 7.2.24-0ubuntu0.18.04.4 (cli) (built: Apr  8 2020 15:45:57) ( NTS )
```
it means you have PHP version 7.2 installed and there is no need to install PHP. You can proceed with checking the extensions for PHP by reading Step 2 of this tutorial. 
If the version is bellow 7.2 proceed with Step 1 of this tutorial to upgrade PHP to needed version. 

In other hand, if you see this in the terminal 
```
The program 'php' can be found in the following packages:
```
it means PHP is not installed and you need to proceed with Step 1 of this tutorial.

# Step 1 - Installing or upgrading PHP
> The process of installing and upgrading the PHP are pretty the same. The difference is that the old PHP installation needs to be disabled and the new to be enabled, this will be described in the end of the installation process. 

First you need to update your current instalation of UBUNTU. To do that open an terminal and type the following commands:
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
