# Films - REST API Example
Sample REST API that manages a film database adapted from https://www.databasestar.com/sample-database-movies/.

It includes a debugging utility that logs every request and error to an HTML file.

## API Documentation

### Main usage

http://_<base_url>_/_<end_point>_

### Endpoints

| Method | Endpoint        | Description                         |
| ------ |:------------ |:----------------------------------- |
| GET    |/    | Returns the API description for GET methods     |
| GET    |/films?title=_<search_text>_ | Returns information of those films whose title contains _<search_text>_ |
| GET    |/films/_<film_id>_ | Returns detailed information of the film with ID _<film_id>_ |
| POST<br><br><br><br><br><br><br><br>   |/films<br><br>Request body<br><br><br><br><br><br> | Adds a new film<br><br>title<br>overview<br>releaseDate<br>runtime<br>directors [array of person IDs]<br>actors [array of person IDs] |
| PUT<br><br><br><br><br><br><br><br>    |/films/_<film_id>_<br><br>Request body<br><br><br><br><br><br> | Updates the film with ID _<film_id>_<br><br>title<br>overview<br>releaseDate<br>runtime<br>directors [array of person IDs]<br>actors [array of person IDs] |
| DELETE    |/films/_<film_id>_ | Deletes the film with ID _<film_id>_ |
| GET    |/persons?name=_<search_text>_ | Returns information of those persons whose name contains _<search_text>_ |
| POST<br><br><br>   |/persons<br><br>Request body | Adds a new person<br><br>name |
| DELETE    |/persons/_<person_id>_ | Deletes the person with ID _<person_id>_ |

### Examples 

- GET http://localhost/php-mysql-films-rest-api/
- GET http://localhost/php-mysql-films-rest-api/films/1895
- GET http://localhost/php-mysql-films-rest-api/films?title=Godfather
- POST http://localhost/php-mysql-films-rest-api/films
- PUT http://localhost/php-mysql-films-rest-api/films/1895
- DELETE http://localhost/php-mysql-films-rest-api/films/1895
- GET http://localhost/php-mysql-films-rest-api/persons?name=McGregor
- POST http://localhost/php-mysql-films-rest-api/persons
- DELETE http://localhost/php-mysql-films-rest-api/persons/3061

### Sample Output

Get film

```json
{
    "films": [
        {
            "movie_id": "1895",
            "title": "Star Wars: Episode III - Revenge of the Sith",
            "overview": "Years after the onset of the Clone Wars, the noble Jedi Knights lead a massive clone army into a galaxy-wide battle against the Separatists. When the sinister Sith unveil a thousand-year-old plot to rule the galaxy, the Republic crumbles and from its ashes rises the evil Galactic Empire. Jedi hero Anakin Skywalker is seduced by the dark side of the Force to become the Emperor's new apprentice – Darth Vader. The Jedi are decimated, as Obi-Wan Kenobi and Jedi Master Yoda are forced into hiding. The only hope for the galaxy are Anakin's own offspring – the twin children born in secrecy who will grow up to become heroes.",
            "release_date": "2005-05-17",
            "runtime": "140",
            "directors": [
                {
                    "person_id": "1",
                    "person_name": "George Lucas"
                }
            ],
            "actors": [
                {
                    "person_id": "3061",
                    "person_name": "Ewan McGregor"
                },
                {
                    "person_id": "524",
                    "person_name": "Natalie Portman"
                },
                {
                    "person_id": "17244",
                    "person_name": "Hayden Christensen"
                },
                ...            
            ]
        }
    ],
    "_links": [
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films{?title=}",
            "type": "GET"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "GET"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films",
            "type": "POST"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "PUT"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "DELETE"
        },
        {
            "rel": "persons",
            "href": "<server_path>/php-mysql-films-rest-api/persons{?name=}",
            "type": "GET"
        },
        {
            "rel": "persons",
            "href": "<server_path>/php-mysql-films-rest-api/persons",
            "type": "POST"
        },
        {
            "rel": "persons",
            "href": "<server_path>/php-mysql-films-rest-api/persons/{id}",
            "type": "DELETE"
        }
    ]
}
```

Search films

```json
{
    "films": [
        {
            "movie_id": "238",
            "title": "The Godfather",
            "release_date": "1972-03-14",
            "runtime": "175"
        },
        {
            "movie_id": "240",
            "title": "The Godfather: Part II",
            "release_date": "1974-12-20",
            "runtime": "200"
        },
        {
            "movie_id": "242",
            "title": "The Godfather: Part III",
            "release_date": "1990-12-24",
            "runtime": "162"
        },
        {
            "movie_id": "70829",
            "title": "The Last Godfather",
            "release_date": "2010-12-29",
            "runtime": "100"
        }
    ],
    "_links": [
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films{?title=}",
            "type": "GET"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "GET"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films",
            "type": "POST"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "PUT"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "DELETE"
        },
        {
            "rel": "persons",
            "href": "<server_path>/php-mysql-films-rest-api/persons{?name=}",
            "type": "GET"
        },
        {
            "rel": "persons",
            "href": "<server_path>/php-mysql-films-rest-api/persons",
            "type": "POST"
        },
        {
            "rel": "persons",
            "href": "<server_path>/php-mysql-films-rest-api/persons/{id}",
            "type": "DELETE"
        }
    ]
}
```

Search persons

```json
{
    "persons": [
        {
            "person_id": "1724888",
            "person_name": "Brian McGregor"
        },
        {
            "person_id": "981005",
            "person_name": "Charles McGregor"
        },
        {
            "person_id": "3061",
            "person_name": "Ewan McGregor"
        },
        ...
    ],
    "_links": [
        {
            "rel": "films",
            "href": "<server_path>/php-mysql-films-rest-api/films{?title=}",
            "type": "GET"
        },
        {
            "rel": "films",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "GET"
        },
        {
            "rel": "films",
            "href": "<server_path>/php-mysql-films-rest-api/films",
            "type": "POST"
        },
        {
            "rel": "films",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "PUT"
        },
        {
            "rel": "films",
            "href": "<server_path>/php-mysql-films-rest-api/films/{id}",
            "type": "DELETE"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/persons{?name=}",
            "type": "GET"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/persons",
            "type": "POST"
        },
        {
            "rel": "self",
            "href": "<server_path>/php-mysql-films-rest-api/persons/{id}",
            "type": "DELETE"
        }
    ]
}
```

### Testing
The directory `postman` includes JSON files for an environment and a collection that can be imported to [Postman](https://www.postman.com/) to test the API .

## Tools
PHP8 / MySQL

## Author
Arturo Mora-Rioja