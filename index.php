<?php
/**
 * Films REST API
 * Refer to README.md for API documentation
 * 
 * @author  Arturo Mora-Rioja
 * @version 1.0.0 October 2020
 * @version 2.0.0 September 2021. HATEOAS links added
 *                                The API can now be served from any directory in the server
 * @version 2.0.1 December 2024. PSR standard enforced
 *                               Class design extended
 *                               Error management improved
 * @version 2.0.2 March 2025. Refactoring
 */

require_once 'classes/Utils.php';
require_once 'classes/APIUtils.php';

define('POS_ENTITY', 1);
define('POS_ID', 2);

define('MAX_PIECES', 3);

define('ENTITY_FILMS', 'films');
define('ENTITY_PERSONS', 'persons');

Utils::debug(
    'REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'] .
    '<br>REQUEST_URI: ' . $_SERVER['REQUEST_URI'] .
    '<br>GET: ' . print_r($_GET, true) .
    'POST: ' . print_r($_POST, true)
);

$url = strtok($_SERVER['REQUEST_URI'], '?');    // GET parameters are removed
// If there is a trailing slash, it is removed, so that it is not taken into account by the explode function
if (substr($url, strlen($url) - 1) == '/') {
    $url = substr($url, 0, strlen($url) - 1);
}
// Everything up to the folder where this file exists is removed.
// This allows the API to be deployed to any directory in the server
$url = substr($url, strpos($url, basename(__DIR__)));

$urlPieces = explode('/', urldecode($url));

header('Content-Type: application/json');
header('Accept-version: v1');
http_response_code(200);

$pieces = count($urlPieces);

if ($pieces == 1) {
    echo APIUtils::APIDescription();
} else {
    if ($pieces > MAX_PIECES) {
        http_response_code(400);
        echo APIUtils::formatError();
    } else {

        $entity = $urlPieces[POS_ENTITY];

        switch ($entity) {
            case ENTITY_PERSONS:
                require_once('src/person.php');
                $person = new Person();
                if ($person->lastErrorMessage !== '') {
                    http_response_code(500);
                    echo APIUtils::formatError($person->lastErrorMessage);
                    exit;
                }

                $verb = $_SERVER['REQUEST_METHOD'];

                switch ($verb) {
                    case 'GET':                             // Search persons
                        if (!isset($_GET['name'])) {
                            http_response_code(400);
                            echo APIUtils::formatError();
                        } else {
                            $result = $person->search($_GET['name']);
                            if (!$result) {
                                http_response_code(500);
                                echo APIUtils::formatError($person->lastErrorMessage);
                            } else {
                                echo APIUtils::addHATEOAS($result, ENTITY_PERSONS);
                            }
                        }
                        break;
                    case 'POST':                            // Add new person
                        if (!isset($_POST['name'])) {
                            http_response_code(400);
                            echo APIUtils::formatError();
                        } else {
                            $result = $person->add($_POST['name']);
                            switch ($result) {
                                case 0:
                                    http_response_code(500);
                                    echo APIUtils::formatError($person->lastErrorMessage);
                                    break;
                                case -1:
                                    http_response_code(400);
                                    echo APIUtils::formatError('The person already exists');
                                    break;
                                default:
                                    http_response_code(201);
                                    echo APIUtils::addHATEOAS($result, ENTITY_PERSONS);
                            }
                        }                        
                        break;
                    case 'DELETE':                          // Delete person
                        if ($pieces < MAX_PIECES) {
                            http_response_code(400);
                            echo APIUtils::formatError();
                        } else {
                            $result = $person->delete($urlPieces[POS_ID]);
                            
                            switch ($result) {
                                case 0:
                                    http_response_code(500);
                                    echo APIUtils::formatError($person->lastErrorMessage);
                                    break;
                                case -1:
                                    http_response_code(400);
                                    echo APIUtils::formatError('The person is associated to a film');
                                    break;
                                default:
                                    echo APIUtils::addHATEOAS($result, ENTITY_PERSONS);
                            }
                        }
                        break;
                    default:
                        http_response_code(405);
                        echo APIUtils::formatError();
                }
                $person = null;
                break;  
            case ENTITY_FILMS:
                require_once 'src/movie.php';
                $movie = new Movie();
                if ($movie->lastErrorMessage !== '') {
                    http_response_code(500);
                    echo APIUtils::formatError($movie->lastErrorMessage);
                    exit;
                }

                $verb = $_SERVER['REQUEST_METHOD'];

                switch ($verb) {
                    case 'GET':
                        if ($pieces < MAX_PIECES) {                 // Search films
                            if (!isset($_GET['title'])) {
                                http_response_code(400);
                                echo APIUtils::formatError();
                                exit;
                            } else {
                                $result = $movie->search($_GET['title']);
                            }
                        } else {                                    // Get film by ID
                            $result = $movie->get($urlPieces[POS_ID]);
                        }
                        if (gettype($result) === 'boolean' && !$result) {
                            http_response_code(500);
                            echo APIUtils::formatError($movie->lastErrorMessage);
                        } else {
                            echo APIUtils::addHATEOAS($result, ENTITY_FILMS);
                        }
                        break;
                    case 'POST':                                    // Add new film
                        if (!isset($_POST['title'])) {
                            http_response_code(500);
                            echo APIUtils::formatError();
                        } else {
                            echo APIUtils::addHATEOAS($movie->add($_POST), ENTITY_FILMS);
                        }
                        break;
                    case 'PUT':                                     // Update film
                        // Since PHP does not handle PUT parameters explicitly,
                        // they must be read from the request body's raw data
                        $movieData = (array) json_decode(file_get_contents('php://input'), TRUE);
                
                        if ($pieces < MAX_PIECES || !isset($movieData['title'])) {
                            http_response_code(400);
                            echo APIUtils::formatError();
                        } else {
                            $result = $movie->update($urlPieces[POS_ID], $movieData);
                            if (gettype($result) === 'boolean' && !$result) {
                                http_response_code(500);
                                echo APIUtils::formatError($movie->lastErrorMessage);
                            } else {
                                if ($result === []) {
                                    http_response_code(404);
                                }
                                echo APIUtils::addHATEOAS($result, ENTITY_FILMS);
                            }
                        }
                        break;
                    case 'DELETE':                                  // Delete film
                        if ($pieces < MAX_PIECES) {
                            http_response_code(400);
                            echo APIUtils::formatError();
                        } else {
                            $result = $movie->delete($urlPieces[POS_ID]);
                            if (gettype($result) === 'boolean' && !$result) {
                                http_response_code(500);
                                echo APIUtils::formatError($movie->lastErrorMessage);
                            } else {
                                if ($result === []) {
                                    http_response_code(404);
                                }
                                echo APIUtils::addHATEOAS($result, ENTITY_FILMS);
                            }
                        }
                        break;
                }
                $movie = null;
                break; 
            default:
                http_response_code(400);
                echo APIUtils::formatError();
        }
    }
}