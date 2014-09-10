sendgrid-web
============

wrapper for the sendgrid web API (get bounce lists, statistics, invalid emails, etc...)

## Usage

Using the api couldn't be simpler: just create a `Config` instance, by passing an array with a `user` and `pass` key (with optional `baseUrl` and `output` keys if required). This object should then be passed to any Api class you need to use. Each class implements the basic API-calls, implemented as methods, and some helper methods.
For example, the `Block` api has a `delete` call, which deletes an email address. This wrapper implements a `deleteEmail` method that maps to this call, but also implements a `deleteEmails` method. This method allows you to pass an array of email addresses, instead of having to call the API one by one.

The `example.php` file requires a `example_params.json` file, containing the config you want to use. The json file is added to .gitignore, so no worries about accidental commits there.
An example of this json file:

```json
{"user": "username", "pass": "MyPassword", "baseUrl": "https://my.sendgrid.url/api/"}
```

Note the format of the baseUrl parameter.
