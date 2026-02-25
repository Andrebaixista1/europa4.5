@echo off
setlocal
set "PHP82=C:\wamp64\bin\php\php8.2.29\php.exe"
if not exist "%PHP82%" (
  echo [ERRO] PHP 8.2 nao encontrado em: %PHP82%
  exit /b 1
)
"%PHP82%" %*
exit /b %ERRORLEVEL%
