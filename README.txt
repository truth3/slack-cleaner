ABOUT:

This PHP utility will help you download your Slack files when you reach the upload limit.

SLACK API AUTHENTICATION:

The actions can be run locally after you add your API key to the "slack_keys.php" file in the auth folder. You will be prompted to enter a date which is used to limit the download or delete to files created before that date.

ACTIONS:

Download - The download action will loop through all of the files which match the date filter and download them into a folder matching the channel name, inside a folder matching the date limit.

Delete - The delete action will loop through all of the files which match the date filter and delete each one. This is meant to be run after the download so you can clean up the account when faced with limit warnings.

NOTES:

This is a very crude setup, so please feel free to use / improve on the code.
