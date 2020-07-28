# Install with composer
> Below you will find steps on how to install composer and use it to setup Behat. Be sure that you cloned your repo in order to proceed with this step, if you didn't set it up, read [**this**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/COMPOSER.md) guide.

**NOTE:** Windows users need to login to WSL in order to execute terminal commands. 
* Open the terminal, go to the folder of your project repo and execute this command:
```
./composer-installer.sh
```
The command above will install composer for this folder.
* To install Behat run this command:
```
php composer.phar install
```
Wait for the process to finish, it may take some time.
* Check the Behat installation by entering this command:
```
bin/behat -V
```
If the installation is successful you should see the folowing text:
```
behat 3.5.0
```
**NOTE:** If you need to make some changes to the composer.json file, when finished, you must run composer update by entering this command in the terminal:
```
php composer.phar update
```
