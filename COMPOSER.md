# Install Behat with composer
> Below you will find steps on how to install composer and use it to setup Behat. Be sure that you cloned your repo in order to proceed with this step, if you didn't set it up, read [**this**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/REPOSITORY.md) guide.

# Check if you have composer.
> To check if you have composer, open terminal and execute:
```
composer -V
```
If you have composer installed you should get text similiar to this:
```
Composer version @package_branch_alias_version@ (1.0.0-beta2) 2016-03-27 16:00:34
```
In this case you can skip the composer installation step.

---
If you do not have composer you should get this:
```
-bash: /usr/bin/composer: No such file or directory
```
In this case you need to perform the composer installation step.

# How to install composer globally.
> **NOTE:** Windows users need to login to WSL in order to execute terminal commands. Click [**here**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/WSL.md#opening-linux-terminal) to see how.

Windows WSL users
* Open the terminal and execute this command:
```
sudo apt-get install composer
```
Mac users
* Open the terminal and execute this command:
```
brew install composer
```
The command above will install composer globally.

# Install Behat.
* To install Behat run this command:
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
