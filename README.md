# About

This PHP utility will help you download your Slack files when you reach the free account upload limit.

![storage limit reached](https://user-images.githubusercontent.com/7986768/40818119-9ff6b816-6523-11e8-92be-c1dbbac9c5fb.png)


## Slack API Authentication

The actions can be run locally after you add your API key to the "slack_keys.php" file in the auth folder.
## Actions

The `fileDownload.php` action will loop through all of the files which match the date filter and download them into a folder matching the channel name, inside a folder matching the date limit.

The `fileDelete.php` action will loop through all of the files which match the date filter and delete each one. This is meant to be run after the download so you can clean up the account when faced with limit warnings.

When you run each action you will see a prompt to enter a date which is used to limit the list of files to download or delete. Only files created before that date will be returned and downloaded or deleted.

### Notes

This is a very crude setup, so please feel free to suggest improvements, submit PRs, and fork for your own use.
