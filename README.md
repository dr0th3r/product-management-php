# Jak zprovoznit pomocí xampp

1. zkopírujte soubory do Vámi vybrané/vytvořené složky v htdocs
2. spustťe xampp control panel
3. přejděte do nastavení apache a v httpd.conf změntě DocumentRoot na htdocs/"vaše složka"/public
4. spustě apache a mysql
5. vytvořte novou databázi a aplikujte migrace, které jsou ve složce /migrations
6. upravte soubor /config/db.php, tak aby obsahoval validní přihlašovací údaje k databázi
7. aplikace by měla běžet na localhostu
