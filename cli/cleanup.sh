mysql -h ls-dev-mysql -u root -proot -e "drop database \`$1\`; show databases;"
yes | rm application/config/config.php
