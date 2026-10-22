@echo off
SETLOCAL
SET APP_NAME="CXDash"
SET INSTALL_DIR=%~1
SET NSSM="%INSTALL_DIR%\dependencies\nssm\nssm.exe"
SET PYTHON_EXE="%INSTALL_DIR%\dependencies\python\python\python.exe"
SET PHP_EXE="%INSTALL_DIR%\dependencies\php\php.exe"

REM -- database server daemon
echo "Installing database server..."
SET NAME=%APP_NAME%MariaDBServer
SET MARIADB_HOME="%INSTALL_DIR%\dependencies\mariadb"
%NSSM% install %NAME% "%MARIADB_HOME%\bin\mariadbd.exe" 
%NSSM% set %NAME% DisplayName "%APP_NAME% MariaDB Server"
%NSSM% set %NAME% AppDirectory "%MARIADB_HOME%"
%NSSM% set %NAME% Description "%APP_NAME% Database Server for Dashboard"
%NSSM% set %NAME% Start SERVICE_AUTO_START
echo "Starting database server..."
%NSSM% start %NAME%

REM -- web server
echo "Installing web server..."
SET NAME=%APP_NAME%WebServer
%NSSM% install %NAME% "%PHP_EXE%"
%NSSM% set %NAME% AppParameters "%INSTALL_DIR%\artisan serve --port=80"
%NSSM% set %NAME% DisplayName "%APP_NAME% Web Server"
%NSSM% set %NAME% AppDirectory "%INSTALL_DIR%"
%NSSM% set %NAME% Description "%APP_NAME% Dasboard"
%NSSM% set %NAME% Start SERVICE_AUTO_START
echo "Starting web server..."
%NSSM% start %NAME%

REM -- queue worker
echo "Install queue worker..."
SET NAME=%APP_NAME%QueueWorker
%NSSM% install %NAME% "%PHP_EXE%"
%NSSM% set %NAME% AppParameters "%INSTALL_DIR%\artisan queue:work --timeout=3600"
%NSSM% set %NAME% AppDirectory "%INSTALL_DIR%"
%NSSM% set %NAME% DisplayName "%APP_NAME% Queue Worker"
%NSSM% set %NAME% Description "Worker for background task for %APP_NAME%"
%NSSM% set %NAME% AppStdout "%INSTALL_DIR%\storage\logs\queue.log"
%NSSM% set %NAME% AppStderr "%INSTALL_DIR%\storage\logs\queue.log"
%NSSM% set %NAME% AppRotateFiles 1
%NSSM% set %NAME% AppRotateBytes 10485760
%NSSM% set %NAME% Start SERVICE_AUTO_START
%NSSM% start %NAME%

ENDLOCAL
exit /b 0