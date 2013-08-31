# This PHP api for MeetRocket
Good new Soc thing.

## Documentation
Additional doc you can find in doc/ folder ;)
Generate API doc with this command

```cd vendor/bin/ && php swagger ../../application/ -o ../../public/api-docs/ --default-base-path "http://127.0.0.1:5000/v1/" --default-api-version 1 && cd ../../```

Generate Helpers doc with this command

```cd vendor/bin/ && php swagger ../../helpers/ -o ../../helpers/helpers-docs/ --default-base-path "http://127.0.0.1:5555/" --default-api-version 1 && cd ../../```


## Requirements

- PHP 5.4.13 or >
- PHP Mysql support
- Mysql server 5.5 or >