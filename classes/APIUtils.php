<?php

class APIUtils
{  
    /**
     * Returns the API's URL path
     */
    static public function urlPath(): string
    {
        $protocol = 
            ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') 
                || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . '/' . basename(__DIR__) . '/';     
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
     * @return string The information to be served by the API including its corresponding HATEOAS links
     */
    static public function addHATEOAS(array|string $information = '', string $entity = ''): string 
    {
        $curDir = self::urlPath();

        if ($entity) {
            $apiInfo[$entity] = $information;
        }
        $apiInfo['_links'] = array(
            array(
                'rel' => ($entity == ENTITY_FILMS ? 'self' : ENTITY_FILMS),
                'href' => $curDir . ENTITY_FILMS . '{?title=}',
                'type' => 'GET'
            ),
            array(
                'rel' => ($entity == ENTITY_FILMS ? 'self' : ENTITY_FILMS),
                'href' => $curDir . ENTITY_FILMS . '/{id}',
                'type' => 'GET'
            ),
            array(
                'rel' => ($entity == ENTITY_FILMS ? 'self' : ENTITY_FILMS),
                'href' => $curDir . ENTITY_FILMS,
                'type' => 'POST'
            ),
            array(
                'rel' => ($entity == ENTITY_FILMS ? 'self' : ENTITY_FILMS),
                'href' => $curDir . ENTITY_FILMS . '/{id}',
                'type' => 'PUT'
            ),
            array(
                'rel' => ($entity == ENTITY_FILMS ? 'self' : ENTITY_FILMS),
                'href' => $curDir . ENTITY_FILMS . '/{id}',
                'type' => 'DELETE'
            ),
            array(
                'rel' => ($entity == ENTITY_PERSONS ? 'self' : ENTITY_PERSONS),
                'href' => $curDir . ENTITY_PERSONS . '{?name=}',
                'type' => 'GET'
            ),
            array(
                'rel' => ($entity == ENTITY_PERSONS ? 'self' : ENTITY_PERSONS),
                'href' => $curDir . ENTITY_PERSONS,
                'type' => 'POST'
            ),
            array(
                'rel' => ($entity == ENTITY_PERSONS ? 'self' : ENTITY_PERSONS),
                'href' => $curDir . ENTITY_PERSONS . '{id}',
                'type' => 'DELETE'
                )
            );        
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