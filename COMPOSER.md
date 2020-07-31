# Install Behat with composer
> Below you will find steps on how to install composer and use it to setup Behat. 
> Be sure that you cloned your repository in order to proceed with this step, if you didn't set it up, read [**this**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/REPOSITORY.md) guide.
> Be sure that you installed PHP in order to proceed with this step, if you didn't installed it, read [**this**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/PHP.md) guide.

# Check if you have composer.
> To check if you have composer, open terminal and execute:
```
composer -V
```
If you see this in the terminal:
```
Composer version 1.10.9 2020-07-16 12:57:00
```
It means you have composer version 1.10.9 installed and there is no need to install composer. If the version is greater than 1.10.9 that is OK and you can proceed with **Step 2** of this tutorial. If the version is bellow 1.10.9 proceed with ***Step 1*** of this tutorial to upgrade composer to the needed version.

In other hand, if you see this in the terminal:
```
-bash: /usr/bin/composer: No such file or directory
```
it means composer is not installed and you need to proceed with **Step 1** of this tutorial.

# Step 1 - How to install or upgrade composer globally.
> **NOTE:** Windows users need to login to WSL in order to execute terminal commands. Click [**here**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/WSL.md#opening-linux-terminal) to see how.

Windows WSL users
* Open the terminal and execute this commands:
```
sudo apt-get install curl
```
```
sudo curl -s https://getcomposer.org/installer | php
```
```
sudo mv composer.phar /usr/local/bin/composer
```
> **NOTE:** You need to logout and login to your WSL after this procedure by executing "logout" and logging back to the WSL.
Mac users
* Open the terminal and execute this command:
```
brew install composer
```
> The commands above will install composer globally.

# Step 2 - Install Behat.
* To install Behat, open the terminal, go to the folder of your newly cloned project and execute this command:
```
composer install
```
Wait for the process to finish, it may take some time. If there are errors try this command:
```
composer install -n --ignore-platform-reqs
```
* Check the Behat installation by entering this command:
```
bin/behat -V
```
If the installation is successful you should see the folowing text:
```
behat 3.5.0
```
> **NOTE:** If you need to make some changes to the composer.json file, when finished, you must run composer update by entering this command in the terminal:
```
composer update
```
