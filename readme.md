# This PHP api for CityHour
Good new Soc thing.

## Documentation
Additional doc you can find in doc/ folder ;)
Generate API doc with this command

```php vendor/zircote/swagger-php/bin/swagger application/ -o public/docs/data/1 --default-base-path "/v1" --default-api-version 1```

Generate Helpers doc with this command

```php vendor/zircote/swagger-php/bin/swagger helpers/ -o helpers/docs/data/1 --default-base-path "/" --default-api-version 1```


## Requirements

- PHP 5.4.13 or >
- PHP Mysql support
- Mysql server 5.5 or >