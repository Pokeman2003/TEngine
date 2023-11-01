@echo off
set server="C:\Users\Workspace\Desktop\XAMPP Server"
set HTML="C:\Users\Workspace\Desktop\Web Development\HTML"
set MySQL="C:\Users\Workspace\Desktop\Web Development\MySQL"
set PHPInc="C:\Users\Workspace\Desktop\Web Development\PHP"
set PHPSet="C:\Users\Workspace\Desktop\Web Development\php.ini"
echo Mount or unmount?
choice
if %errorlevel%==1 goto mount
if %errorlevel%==2 goto unmount

:mount
if exist %server%\include rmdir /s /q %server%\include&echo Unmounted includes.
if exist %server%\mysql\data rmdir /s /q %server%\mysql\data&echo Unmounted MySQL
if exist %server%\htdocs rmdir /s /q %server%\htdocs&echo Unmounted HTML
if exist %server%\php\php.ini del %server%\php\php.ini&echo Unmounted PHP.

echo %server%

if exist %HTML% (mklink /j %server%\htdocs %HTML%&echo Mounted HTML.) else mkdir %server%\htdocs&echo Substituted HTML.
if exist %MySQL% (mklink /j %server%\mysql\data %MySQL%&echo Mounted SQL.) else xcopy /s /v /i %server%\mysql\backup\*.* %server%\mySQL\data&echo Substituted MySQL.
if exist %PHPInc% (mklink /j %server%\include %PHPInc%&echo Mounted includes.) else echo No includes to mount.
if exist %PHPSet% (mklink /h %server%\php\php.ini %PHPSet%&echo Mounted settings.) else copy /v /b %server%\php\phpDef.ini %server%\php\php.ini&echo Substituted settings.
exit /b

:unmount
if exist %server%\include rmdir %server%\include
if exist %server%\mysql\data rmdir %server%\mysql\data
if exist %server%\htdocs rmdir %server%\htdocs
if exist %server%\php\php.ini del %server%\php\php.ini
exit /b