@echo off
setlocal enabledelayedexpansion

REM Build script for Gift Message for WooCommerce plugin
REM Creates a WordPress.org compliant zip file

REM Plugin info
set PLUGIN_SLUG=gift-message-for-woo
set PLUGIN_DIR=%~dp0
set BUILD_DIR=%PLUGIN_DIR%build
set DIST_DIR=%PLUGIN_DIR%dist

echo Building %PLUGIN_SLUG% plugin...

REM Clean up any existing build/dist directories
echo Cleaning up old builds...
if exist "%BUILD_DIR%" rd /s /q "%BUILD_DIR%"
if exist "%DIST_DIR%" rd /s /q "%DIST_DIR%"

REM Create directories
mkdir "%BUILD_DIR%" 2>nul
mkdir "%DIST_DIR%" 2>nul

REM Copy plugin files to build directory
echo Copying plugin files...
xcopy /E /I /Q /Y "%PLUGIN_DIR%*" "%BUILD_DIR%\" >nul 2>&1

REM Navigate to build directory
cd /d "%BUILD_DIR%"

REM Remove development and build directories
echo Removing development files...
if exist "build" rd /s /q "build"
if exist "dist" rd /s /q "dist"
if exist ".git" rd /s /q ".git"
if exist ".github" rd /s /q ".github"
if exist "tests" rd /s /q "tests"
if exist "node_modules" rd /s /q "node_modules"
if exist "vendor\composer" rd /s /q "vendor\composer"
if exist ".idea" rd /s /q ".idea"
if exist ".vscode" rd /s /q ".vscode"

REM Remove development files
del /f /q ".gitignore" 2>nul
del /f /q ".gitattributes" 2>nul
del /f /q ".editorconfig" 2>nul
del /f /q ".distignore" 2>nul
del /f /q ".phpcs.xml" 2>nul
del /f /q ".phpcs.xml.dist" 2>nul
del /f /q "phpunit.xml" 2>nul
del /f /q "phpunit.xml.dist" 2>nul
del /f /q "composer.json" 2>nul
del /f /q "composer.lock" 2>nul
del /f /q "package.json" 2>nul
del /f /q "package-lock.json" 2>nul
del /f /q "webpack.config.js" 2>nul
del /f /q "Gruntfile.js" 2>nul
del /f /q "gulpfile.js" 2>nul
del /f /q ".eslintrc" 2>nul
del /f /q ".eslintrc.js" 2>nul
del /f /q ".eslintrc.json" 2>nul
del /f /q ".stylelintrc" 2>nul
del /f /q ".stylelintrc.json" 2>nul
del /f /q "CLAUDE.md" 2>nul
del /f /q "build.sh" 2>nul
del /f /q "build.bat" 2>nul
del /f /q "*.md" 2>nul
del /f /q "*.log" 2>nul
del /f /q "*.lock" 2>nul

REM Remove hidden files
del /f /q /a:h ".*" 2>nul

REM Get version from main plugin file
set VERSION=1.0.0
for /f "tokens=2 delims=: " %%a in ('findstr /i "Version:" "%PLUGIN_SLUG%.php" 2^>nul') do set VERSION=%%a

REM Create zip file
echo Creating zip file...
cd ..
set ZIP_NAME=%PLUGIN_SLUG%-%VERSION%.zip

REM Use PowerShell to create the zip file
powershell -command "& { Add-Type -A 'System.IO.Compression.FileSystem'; [IO.Compression.ZipFile]::CreateFromDirectory('%BUILD_DIR%', '%DIST_DIR%\%ZIP_NAME%') }"

REM Clean up build directory
rd /s /q "%BUILD_DIR%"

REM Check if zip was created successfully
if exist "%DIST_DIR%\%ZIP_NAME%" (
    echo.
    echo Build complete!
    echo Plugin zip created: %DIST_DIR%\%ZIP_NAME%
    
    REM Show file size
    for %%A in ("%DIST_DIR%\%ZIP_NAME%") do echo File size: %%~zA bytes
    
    echo.
    echo Zip contents:
    powershell -command "& { Add-Type -A 'System.IO.Compression.FileSystem'; $zip = [IO.Compression.ZipFile]::OpenRead('%DIST_DIR%\%ZIP_NAME%'); $zip.Entries | Select-Object -First 20 | ForEach-Object { $_.FullName }; $zip.Dispose() }"
) else (
    echo Build failed!
    exit /b 1
)

endlocal