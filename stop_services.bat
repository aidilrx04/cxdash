@echo off

SET APP_NAME=CXDash
SET MARIADB_SERVICE=%APP_NAME%MariaDBServer
SET WEB_SERVICE=%APP_NAME%WebServer
SET QUEUE_SERVICE=%APP_NAME%QueueWorker

SET SHOULD_REMOVE=FALSE
IF "%~1"=="--remove" SET SHOULD_REMOVE=TRUE

REM -- stop database server...

net stop %QUEUE_SERVICE%
net stop %WEB_SERVICE%
net stop %MARIADB_SERVICE%

if "%SHOULD_REMOVE%"=="TRUE" (
    echo Removing services...
    sc delete %QUEUE_SERVICE%
    sc delete %WEB_SERVICE%
    sc delete %MARIADB_SERVICE%
)

echo Done.