@echo off
cd application
for /r %%v in (*.php) do @php -d error_reporting=32767 -l %%v|findstr /V "No syntax error"
