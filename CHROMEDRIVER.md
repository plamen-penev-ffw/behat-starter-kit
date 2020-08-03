# Setting up ChromeDriver
> ChromeDriver is a separate executable that is used to control Chrome browser when executing Behat tests.
* Ensure that you have the Chrome browser installed.
> **NOTE:** In order the ChromeDriver to work with the Chrome browser they both need to have the same base version i.e. if the Chrome browser is version **84.0.4147.30** you need ChromeDriver version that starts with **84**.
* Go to the ChromeDriver download page by clicking [**here**](https://chromedriver.chromium.org/downloads). 
* Search in the list for proper version that matches your Chrome browser version.
* Download the version according to your operating system:
  - Windows users: chromedriver_win32.zip
  - Mac users: chromedriver_mac64.zip
> **NOTE:** Window user need to download ChromeDriver for windows even if the test are run from Linux WSL.
# Set ChromeDriver to be used globally.
> Once you downloaded ChromeDriver you need to set a link so you can use it globally, otherwise every time you need to launch ChromeDriver you will need to go to the folder you downloaded it and run it from there.
## Windows users:
* Once you download ChromeDriver, extract the archive contents to a folder where the ChromeDriver will reside permanently. For example C:\chromedriver
> **NOTE:** Once you assign ChromeDriver as Global variable, do not move and/or rename the folder or the Chromedriver! If you do, you will need to reassing it as global variable.
* Type **Edit the system envrionemnt variables** in the search bar(next to the startbutton). Click to open it.
* In the popup, locate button labeled **Envrionment variables...** and click on it.
* In the **System variables** area click on **Path** then on the **Edit** button.
* In the next screen click on the **New** button, then on the **Browse** button, then in the folder browse window locate where you extracted the chromedriver, then click **Ok** and again **Ok**.
* Now you are back to the **System variables** area, click on **New** and in the **Variable name** field enter **chromedriver**.
* Click on the **Browse file...** button and locate the chromedriver file in the file browse window, doubleclick on file then click **Ok**.
* After finishing the steps above you will be on the **System variables** area, now click **Ok** and restart your computer in order the new variables to get in effect.
* To check if everything is OK, open a command promt and type: **chromedriver**, you should get the folowing something similiar:
```
Starting ChromeDriver 84.0.4147.30 (48b3e868b4cc0aa7e8149519690b6f6949e110a8-refs/branch-heads/4147@{#310}) on port 9515
Only local connections are allowed.
Please see https://chromedriver.chromium.org/security-considerations for suggestions on keeping ChromeDriver safe.
ChromeDriver was started successfully.
```
> **NOTE:** Please observe the port number which chromedriver is using, because it is used in the Behat config. Before you start running tests, first you need to start chromedriver!
## Mac users:

