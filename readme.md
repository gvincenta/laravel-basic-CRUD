# How to deploy: 
## On local device:
1. clone this project to your xampp/htdocs directory (or anywhere you can run a PHP Laravel project in).
2. open terminal and cd to this project directory. 
3. run  `composer install` to install backend dependencies
4. run  `npm install` to install frontend dependencies
5. run  `php artisan key:generate` to generate APP_KEY in the .env file.
6. add a .env file with the configurations shown below to the project folder.
7. run  `php artisan migrate`.
8. (optional) run  `npm run watch` in case you've changed anything in the frontend.
8. run the command `php artisan serve`.
9. In the terminal, it will respond with : `Laravel development server started: [link] ` click on the link to run the web-app on your brower.
10. Click on *Guide Me* to learn how to use the web-app.

## On the cloud servers: 
1. clone or fork this project. 
2. run  `php artisan key:generate` to generate APP_KEY in the .env file.
3. add in .env configurations as shown below to the cloud server and to the project folder.
4. run  `php artisan migrate`. 
5. deploy to your desired hosting services, not forgetting to include the Procfile (it has been included in this project) to specify the server and where to serve the website.

*To improve the web-app experience, please open the web-app on Google Chrome / Opera.*

### .env configurations (for *Local Device Deployment* / *Test Coverage*):
```
APP_DEBUG = true
APP_ENV = local 
APP_KEY =  (to be generated through php artisan key:generate)
APP_NAME = LARAVEL
APP_URL=http://127.0.0.1:[port number]


DB_CONNECTION = mysql
DB_DATABASE = ...
DB_HOST = ...
DB_PASSWORD = ...
DB_USERNAME = ...
DB_PORT =   ...
```
### .env configurations (for *Cloud Server Deployment*):
```
APP_DEBUG = true
APP_ENV =  
APP_KEY = (to be generated through php artisan key:generate)
APP_NAME = LARAVEL
APP_URL= ...


DB_CONNECTION = mysql
DB_DATABASE = ...
DB_HOST = ...
DB_PASSWORD = ...
DB_USERNAME = ...
DB_PORT =   (optional)
```
*Note: for .env, replace  "..." with __suitable constants__.*

# To run *Test Coverage* : 
1. clone or fork this project. 
2. run  `php artisan key:generate` to generate APP_KEY in the .env file.
3. add in .env configurations as shown below to the project folder.
4. run `vendor/bin/phpunit`. Note that:
    1. *This will take a few minutes to run.*
    2. __This will overwrite all files in the reports folder.__
2. Go to *reports* folder and open *index.html* file in your browser to view the test coverage report. 

# I want to know more about: 
1. [XML export methodology](documentation/XMLExport.md)
2. [Database Structure Diagram](documentation/DBStructure.md)