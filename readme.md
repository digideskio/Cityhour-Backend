# This PHP api for CityHour
Good new Soc thing.

## Documentation
Additional doc you can find in doc/ folder ;)
Generate API doc with this command

```php vendor/zircote/swagger-php/bin/swagger --project-dir application/ -o public/api-docs/ --default-base-path "http://api.trubear.com/v1/" --default-api-version 1```

Generate Helpers doc with this command

```php vendor/zircote/swagger-php/bin/swagger --project-dir helpers/ -o helpers/helpers-docs/ --default-base-path "http://helpers.trubear.com/" --default-api-version 1```


## Requirements

- PHP 5.4.13 or >
- PHP Mysql support
- Mysql server 5.5 or >