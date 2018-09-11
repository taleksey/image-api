# image-api

## Synopsis

This api allow to insert, update, delete images. And get images by their id. Also you have to set width and height
for image that you want to get.

## Installation

You have to install composer, mysql for working with this application. 
After you install the project you have to configure file `.env`. To set there  username, password and db name.   
Last step run migration `php bin/console doctrine:migrations:migrate`

## API Reference
GET `api/image/imageId/width/height` path to get image
***
POST `api/image` set name of input field **filename**
***  
PUT `api/image/imageId` set any name of input field
***
DELETE `api/image/imageId`

## Built With

* [Symfony](https://symfony.com/doc/current/index.html) - The web framework used
* [Imagine](https://imagine.readthedocs.io/en/latest/usage/introduction.html#) - Class to work with image
* [Php](http://php.net/) - Language that using for developing

# Authors

* **Aleksey Terehov**

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details