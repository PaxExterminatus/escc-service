@echo off
REM Runs artisan through the project-local php.bat (PHP 8.3.4 + oci8).
call "%~dp0php.bat" "%~dp0artisan" %*
