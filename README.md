# PHP-IPStack

### Example
``` php
$key = '1234';
$client = new IPStack($key);
$client->setIP($ip);
$record = $client->getRecord();
print_r($record);
exit(0);
```
