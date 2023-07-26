## Installation

To install the project :

1. git clone
2. cd test_drupal
3. Set up your environment variable in .env file situated in root project.
4. **docker-compose up -d** to build and run all the requirements of the project
5. **docker-compose exec php bash** to run php container
6. Inside PHP container run **composer install** : this will install Drupal and all the dependencies of the project.
7. Copy the file ./sites/default/default.settings.php to ./sites/default/settings.php
8. Update Settings.php created with you database credentials (the same as in .env file)

Import the database: (To get the events content)

Inside the php container:
- mysql -u user -p database-name<dump.sql

Synchronize the configuration:

Inside the php container:
- cd web/
- drush cim -y

## Technical choices

1/ A custom module event was created and activated to treat the test instructions

2/ The number of events to show in the block is configurable in the block section (by default : 3)

3/ The block is configured to be displayed in the content type (event) : (block.block.relatedeventsblock.yml)

4/ The file block-related-events.html.twig was created to handle the templating of the block, I choose to only show the title and the event type but other informations can be passed and displayed in the template

4/ Queue UI module was installed to insure that the queue to unpublish events is working properly but it's manadatory to use it.

## Time spent : 6 hours

