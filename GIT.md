# Add SSH key to your github account.
> Adding a SSH key to your github account will allow you to clone, pull and push to your project repo.
> If you already added your ssh key to your account you can skip this and proceed with setting up your repository by reading [**this**](https://github.com/plamen-penev-ffw/behat-starter-kit/blob/master/REPOSITORY.md) guide.

# Creating the SSH key
**NOTE:** Windows users need to login to WSL in order to execute terminal commands.
* Open a terminal and enter the folowing commands:
```
cd ~/.ssh
```
```
ss-keygen
```
Folow the onscreen instructions. 
> **NOTE:** Do not add passphrase to the key, just press enter twice when asked for passphrase. 
* Copy the SSH key
```
cat ~/.ssh/id_rsa.pub
```
Then you should see something similiar:
```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCdoQ5i9tumsIxHwAF8Z64YThPewQ4MiYI7TTXivSA/ltkH1sNqkJvK6FpEQnBPQ4WFM6DYLL0hVOS89DfE5KinEvh6YnhBfzD9tKt339MGHCULg+x4TWfQkCzVUUvSnhqz/21H1XpoOf6vv9vW0p0nj8GZIhgndOoJauDYGYdWmUEUaJR4wknBwSPeR3I6iC8BeZrT6HFM2ZpChqxDjvsqRjE+T8pd+6JyRP8wCpuwxLXME2m+GTKTbhVk0U4varYCIEAO+vtF4bFg4Fn1VQxgroQO4YByaosJRvRQMXhp4J6JPrOGlBZeIWf63/fxOx6ZchVkgP6hxgwpHoockLwr plamen-penev@Plamen-QA
```
> This is your newly generated key. Now select it and copy it to your clipboard.
* Now go to the github page and log in to your account
* Click on your account picture in the upper right corner to expand the menu, and then click on **"Settings"**.
* In the menu on the left click on **"SSH and GPG keys"**
* On the next screen click on **"New SSH key"** green button.
* On the next screen in the **"Key"** field, paste your key, then click on **"Add SSH key"** button.
> You should receive e-mail notifying that there is a SSH key added to your github account.



























![C7](https://user-images.githubusercontent.com/33353397/88484279-0ed2ad80-cf76-11ea-985e-7ea3deb0396c.jpg)
 
