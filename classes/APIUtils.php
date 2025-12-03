<?php

require_once 'Entity.php';

class APIUtils
{  
    /**
     * Returns the API's URL path
     */
    static public function urlPath(): string
    {
        $protocol =
            ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || $_SERVER['SERVER_PORT'] == 443)
            ? 'https://' : 'http://';

        // Normalize paths (convert Windows backslashes to forward slashes)
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $classDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));

        // Compute relative path of the project to the document root
        $relative = str_replace($docRoot, '', $classDir);

        // Ensure leading slash and remove duplicate slashes
        $relative = '/' . ltrim($relative, '/');
        $relative = preg_replace('#/+#', '/', $relative);

        return $protocol . $_SERVER['HTTP_HOST'] . $relative . '/';
    }


    /**
     * Returns the REST API description
     */
    static public function APIDescription(): string 
    {
        return self::addHATEOAS();
    }

    /**
     * Adds HATEOAS links to the data it receives as a parameter
     * 
     * @param   $information    Entity information to add the HATEOAS links to
     * @param   $entity         Name of the entity the HATEOAS links will be added to.
     *                          If false, only the HATEOAS links will be returned
     * @param   $id             The ID of the present resource, if any
     * @return string The information to be served by the API including its corresponding HATEOAS links
     */
    static public function addHATEOAS(array|string $information = '', string $entity = '', int $id = 0): string 
    {
        $curDir = self::urlPath();

        $apiInfo = [
            '_links' => []
        ];
        if ($entity) {
            $apiInfo[$entity] = $information;
            if ($entity === Entity::ENTITY_FILMS && $id) {
                require_once 'classes/Movie.php';
                $film = new Movie();
                $first = $film->first();
                $last = $film->last();
                $prev = $film->prev($id);
                $next = $film->next($id);

                $apiInfo['_links'][] = 
                [
                    'rel' => 'first',
                    'href' => $curDir . $entity . '/' . $first,
                    'type' => 'GET'
                ];
                $apiInfo['_links'][] = 
                [
                    'rel' => 'prev',
                    'href' => $curDir . $entity . '/' . $prev,
                    'type' => 'GET'
                ];
                $apiInfo['_links'][] = 
                [
                    'rel' => 'next',
                    'href' => $curDir . $entity . '/' . $next,
                    'type' => 'GET'
                ];                
                $apiInfo['_links'][] = 
                [
                    'rel' => 'last',
                    'href' => $curDir . $entity . '/' . $last,
                    'type' => 'GET'
                ];
            }
        }
        if ($entity === '' || $entity === Entity::ENTITY_FILMS) {
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_FILMS ? 'self' : Entity::ENTITY_FILMS),
                    'href' => $curDir . Entity::ENTITY_FILMS . '{?title=}',
                    'type' => 'GET'
                );
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_FILMS ? 'self' : Entity::ENTITY_FILMS),
                    'href' => $curDir . Entity::ENTITY_FILMS . '/{id}',
                    'type' => 'GET'
                );
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_FILMS ? 'self' : Entity::ENTITY_FILMS),
                    'href' => $curDir . Entity::ENTITY_FILMS,
                    'type' => 'POST'
                );
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_FILMS ? 'self' : Entity::ENTITY_FILMS),
                    'href' => $curDir . Entity::ENTITY_FILMS . '/{id}',
                    'type' => 'PUT'
                );
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_FILMS ? 'self' : Entity::ENTITY_FILMS),
                    'href' => $curDir . Entity::ENTITY_FILMS . '/{id}',
                    'type' => 'DELETE'
                );
        }
        if ($entity === '' || $entity === Entity::ENTITY_PERSONS) {
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_PERSONS ? 'self' : Entity::ENTITY_PERSONS),
                    'href' => $curDir . Entity::ENTITY_PERSONS . '{?name=}',
                    'type' => 'GET'
                );
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_PERSONS ? 'self' : Entity::ENTITY_PERSONS),
                    'href' => $curDir . Entity::ENTITY_PERSONS,
                    'type' => 'POST'
                );
            $apiInfo['_links'][] = 
                array(
                    'rel' => ($entity == Entity::ENTITY_PERSONS ? 'self' : Entity::ENTITY_PERSONS),
                    'href' => $curDir . Entity::ENTITY_PERSONS . '{id}',
                    'type' => 'DELETE'
                );
        }
        return json_encode($apiInfo);
    }
    
    /**
     * Returns a format error
     * 
     * @param $errorMessage The error message to format. If none, "Incorrect format"
     * @return The error message formatted as a JSONised array
     */
    static public function formatError(string $errorMessage = ''): string
    {
        $output['message'] = $errorMessage === '' ? 'Incorrect format' : $errorMessage;
        return self::addHATEOAS($output, '_error');
    }
}