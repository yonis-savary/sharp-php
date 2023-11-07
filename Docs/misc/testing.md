[< Back to summary](../home.md)

# ✅ Testing the framework/apps

To test the framework :
1. Go to your project root directory
2. Launch `php do build` to install dependencies
3. Launch `php do test`

Launching the `test` command will execute every phpunit instances in your application(s)


## Testing the Framework : FTP

By default, FTP cannot be tested directly, it needs to be configured first

To test [`FTPDriver`](../../Classes/Env/Drivers/FTPDriver.php), edit `ftp-test` in your configuration

```json
"ftp-test" : {
    "username": "foo",
    "password": "bar",
    "port": 21
}
```

> [!NOTE]
> `port` is set to 21 by default (which is the default FTP port)


[< Back to summary](../home.md)
