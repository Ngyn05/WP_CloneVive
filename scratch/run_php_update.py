import glob
import subprocess

php_bins = glob.glob('C:/Users/hnguy/AppData/Roaming/Local/lightning-services/php-*/bin/win64/php.exe')
if php_bins:
    php_path = php_bins[-1]
    res = subprocess.run([php_path, 'scratch/update_test_prod_content.php'], capture_output=True, text=True)
    print("STDOUT:", res.stdout)
    print("STDERR:", res.stderr)
else:
    print("No PHP found")
