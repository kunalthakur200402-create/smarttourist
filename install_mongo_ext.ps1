$ErrorActionPreference = "Stop"

$phpExtDir = "C:\xampp\php\ext"
$phpIniPath = "C:\xampp\php\php.ini"

# Using the verified link from pecl.php.net
Write-Host "Downloading MongoDB PHP Extension (1.21.0 for PHP 8.2 TS x64)..."
$zipUrl = "https://downloads.php.net/~windows/pecl/releases/mongodb/1.21.0/php_mongodb-1.21.0-8.2-ts-vs16-x64.zip"
$tempZipPath = "$env:TEMP\php_mongodb.zip"
$tempExtractPath = "$env:TEMP\php_mongodb_extract"

Invoke-WebRequest -Uri $zipUrl -OutFile $tempZipPath

if (Test-Path $tempExtractPath) { Remove-Item -Recurse -Force $tempExtractPath }
New-Item -ItemType Directory -Path $tempExtractPath | Out-Null
Expand-Archive -Path $tempZipPath -DestinationPath $tempExtractPath -Force

Write-Host "Copying php_mongodb.dll to $phpExtDir..."
Copy-Item -Path "$tempExtractPath\php_mongodb.dll" -Destination "$phpExtDir\php_mongodb.dll" -Force

Write-Host "Updating php.ini..."
$iniContent = Get-Content $phpIniPath
if (!($iniContent -match "extension=mongodb")) {
    Add-Content -Path $phpIniPath -Value "`nextension=mongodb"
    Write-Host "Added 'extension=mongodb' to php.ini"
} else {
    Write-Host "'extension=mongodb' already exists or is mirrored in php.ini"
}

Write-Host "Cleaning up temp files..."
Remove-Item -Force $tempZipPath
Remove-Item -Recurse -Force $tempExtractPath

Write-Host "MongoDB PHP Extension Setup Complete. IMPORTANT: Please restart your Apache server in XAMPP Control Panel!"
