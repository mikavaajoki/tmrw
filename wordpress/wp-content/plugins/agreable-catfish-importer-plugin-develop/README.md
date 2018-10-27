Agreable Catfish Importer Plugin
===============

For importing Catfish content in to Croissant

# Setup

Add variable
```
CATFISH_IMPORTER_TARGET_URL=http://www.shortlist.com/
```

# Commands

Commands should be run from croissant command runner 

### Import runner

run `php croissant plugin:catfish:import` to start import. You might want to use nohup/disown. Import might take few hours and your ssh connection might close. 

import runner have additional options

-p amount of processes 
-c amount of posts imported per one process
--update run only posts updated from last time
-l limit amount of posts
`--date-limit="d/m/Y"` it will ignore all posts older than the date limit
-vvv to change default symfony command verbosity

### Manual posts update

run `wp eval-file src/cli-runner/catfish-import-posts.php {space separated urls}`. This command will update posts by their urls. use --debug to increase verbosity

### Get imported posts stats

run `wp eval-file src/cli-runner/catfish-posts-update-times.php`. This will give you list of `post_url{SEPARATOR}update_time` of current 'stock'


### User roles

For security reasons when new user is created from old system they are all assigned one and only one role called 'purgatory' to make it possible for those people to log in, you will have to adjust their privileges

### Nohup command

nohup php croissant plugin:catfish:import >>/tmp/catfish_cmd_output.log 2>> /tmp/catfish_cmd_error.log &
