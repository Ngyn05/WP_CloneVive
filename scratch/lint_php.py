import glob
import subprocess

php_bins = glob.glob('C:/Users/hnguy/AppData/Roaming/Local/lightning-services/php-*/bin/win64/php.exe')
if php_bins:
    php_path = php_bins[-1]
    res1 = subprocess.run([php_path, '-l', 'functions.php'], capture_output=True)
    print("functions.php lint:", res1.stdout.decode('utf-8', errors='ignore'))
    res2 = subprocess.run([php_path, '-l', 'inc/product-cms.php'], capture_output=True)
    print("inc/product-cms.php lint:", res2.stdout.decode('utf-8', errors='ignore'))
