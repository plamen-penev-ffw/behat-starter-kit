# WSL setup.
> In order to get WSL you need to update your Windows to the latest version.

# Update Windows to the latest version.
* [**Click here**](https://www.microsoft.com/en-us/software-download/windows10/) to go to the Windows update page.
* Click on the **Update now** button. Update installer will be downloaded.
* Locate and run the downloaded file.
* Folow the onscreen instructions. 
* Wait the update process to finish. Your PC might be restarted several times.

# Install WSL.
* In the search bar(next to the start button) of Windows type **Windows PowerShell**
* Right click on the icon of the Windows PowerShell and select **Run as administrator**
* Windows PowerShell instance will be opened, then execute the folowing command:
```
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
```
* Wait the process to finish and you now have WSL installed.

#Install Ubuntu on WSL
