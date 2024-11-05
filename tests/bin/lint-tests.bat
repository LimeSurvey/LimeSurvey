@echo off
cd tests
for /r %%v in (*.php) do @php -d error_reporting=32767 -l %%v
