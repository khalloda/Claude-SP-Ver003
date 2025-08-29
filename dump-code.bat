@echo off
setlocal enabledelayedexpansion

REM Usage: dump-code.bat [ROOT] [OUTFILE]
set "ROOT=%~1"
if not defined ROOT set "ROOT=."
set "OUT=%~2"
if not defined OUT set "OUT=code_dump.txt"

REM Optional: switch to UTF-8 for better characters
REM chcp 65001 >NUL

echo # Code dump generated on %date% %time%>"%OUT%"
for %%I in ("%ROOT%") do set "ABSR=%%~fI"
echo # Root: %ABSR%>>"%OUT%"
echo.>>"%OUT%"

echo ==================== HIERARCHY ====================>>"%OUT%"
tree "%ROOT%" /F /A >> "%OUT%"
echo.>>"%OUT%"

REM Escape the & with ^ so CMD doesn’t misinterpret it
echo ================ FILES ^& CONTENTS =================>>"%OUT%"

FOR /R "%ROOT%" %%F IN (*.php *.css *.js) DO (
  echo.>>"%OUT%"
  set "FULL=%%~fF"
  set "REL=!FULL:%ABSR%\=!"
  set "REL=!REL:\=/!"
  echo ----- !REL! ----- >> "%OUT%"
  type "%%F" >> "%OUT%"
)

echo.>>"%OUT%"
echo ====================== END =======================>>"%OUT%"

echo Done. Output: %OUT%
endlocal
