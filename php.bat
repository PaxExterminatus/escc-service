@echo off
REM Project-local PHP: forces PHP 8.3.4 + Oracle Instant Client 19.9 (oci8),
REM without touching the system-wide PATH/PHP used by other projects.
set "PATH=C:\app-server\php-8.3.4;C:\nginx\oracle\instantclient_19_9;%PATH%"
"C:\app-server\php-8.3.4\php.exe" %*
