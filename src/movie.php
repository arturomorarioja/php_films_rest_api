<?php
/**
 * Movie class
 * 
 * @author Arturo Mora-Rioja
 * @version 1.0.0 August 2020
 * @version 1.0.1 December 2024. PSR standard enforced
 *                               Class design extended
 *                               Error handling improved
 */

require_once 'connection.php';
require_once 'utils.php';

class Movie extends DB 
{    
    /**
     * Retrieves the movies whose title includes a certain text
     * 
     * @param   text upon which to execute the search
     * @return  an array with movie information,
     *      or false if there was an error
     */
    public function search(string $searchText): array|false
    {
        $query = <<<'SQL'
            SELECT movie_id, title, release_date, runtime
            FROM movie
            WHERE title LIKE ?
            ORDER BY title;
        SQL;

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['%' . $searchText . '%']);                
            
            $results = $stmt->fetchAll();                
            
            $this->disconnect();
            
            return $results;                
        } catch (PDOException $e) {
            $this->disconnect();
            Utils::debug($e);
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return false;
        }
    }

    /**
     * Retrieves a specific film
     * 
     * @param  int The id of the film whose information must be retrieved
     * @return an array with film information,
     *      or false if there was an error
     */
    public function get(int $id): array|false
    {
        // Movie data
        $query = <<<'SQL'
            SELECT movie_id, title, overview, release_date, runtime
            FROM movie 
            WHERE movie_id = ?;
        SQL;

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            $results = $stmt->fetch();
        } catch (PDOException $e) {
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return false;
        }

        if (gettype($results) !== 'array') {
            $this->disconnect();
            return [];
        } else {
            // Director data
            $query = <<<'SQL'
                SELECT person.person_id, person.person_name
                FROM person INNER JOIN movie_director ON person.person_id = movie_director.person_id
                WHERE movie_director.movie_id = ?;
            SQL;
            
            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$id]);
                $results['directors'] = $stmt->fetchAll();
            } catch (PDOException $e) {
                $this->disconnect();
                $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
                return false;
            }
            
            // Cast data
            $query = <<<'SQL'
                SELECT person.person_id, person.person_name
                FROM person INNER JOIN movie_cast ON person.person_id = movie_cast.person_id
                WHERE movie_cast.movie_id = ?;
                ORDER BY movie_cast.cast_order;
            SQL;
            
            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$id]);
                $results['actors'] = $stmt->fetchAll();
                
                $this->disconnect();
                return [$results];
                
            } catch (PDOException $e) {
                $this->disconnect();
                Utils::debug($e);
                $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
                return false;
            }
        }
    }

    /**
     * Inserts a new movie
     * 
     * @param   movie info
     * @return  an array with the fiml's ID,
     *      or false if there was an error
     */
    public function add(array $info): array|false
    {            
        try {
            $this->pdo->beginTransaction();

            $query = <<<'SQL'
                INSERT INTO movie (title, overview, release_date, runtime) VALUES (?, ?, ?, ?);
            SQL;

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$info['title'], $info['overview'], $info['releaseDate'], $info['runtime']]);

            $newID = $this->pdo->lastInsertId();

            // Directors
            if (isset($info['directors'])) {
                foreach($info['directors'] as $director) {
                    $query = <<<'SQL'
                        INSERT INTO movie_director (movie_id, person_id) VALUES (?, ?);
                    SQL;
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([$newID, $director]);
                }
            }

            // Actors
            if (isset($info['actors'])) {
                foreach($info['actors'] as $actor) {
                    $query = <<<'SQL'
                        INSERT INTO movie_cast (movie_id, person_id) VALUES (?, ?);
                    SQL;
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([$newID, $actor]);
                }
            }

            $this->pdo->commit();
            $this->disconnect();
    
            return ['id' => $newID];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->disconnect();
            Utils::debug($e);
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a movie
     * 
     * @param   movie ID
     * @param   movie info
     * @return  an array with the film's ID,
     *      an empty array if the film does not exist,
     *      or false if there was an error
     */
    public function update(int $movieID, array $info): array|false
    {
        // 
        $query = <<<'SQL'
            SELECT COUNT(*) AS Total
            FROM movie
            WHERE movie_id = ?;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$movieID]);
        if ($stmt->fetch()['Total'] === 0) {
            $this->disconnect();
            return [];
        }

        try {
            $this->pdo->beginTransaction();

            $query = <<<'SQL'
                UPDATE movie
                SET title = ?,
                    overview = ?,
                    release_date = ?,
                    runtime = ?
                WHERE movie_id = ?
            SQL;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$info['title'], $info['overview'], $info['releaseDate'], $info['runtime'], $movieID]);
                
            // Directors
            $query = <<<'SQL'
                DELETE FROM movie_director
                WHERE movie_id = ?;
            SQL;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$movieID]);
            
            if (isset($info['directors'])) {
                foreach($info['directors'] as $director) {
                    $query = <<<'SQL'
                        INSERT INTO movie_director (movie_id, person_id) VALUES (?, ?);
                    SQL;                        
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([$movieID, $director]);
                }
            }
            
            // Actors
            $query = <<<'SQL'
                DELETE FROM movie_cast
                WHERE movie_id = ?;
            SQL;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$movieID]);
            
            if (isset($info['actors'])) {
                foreach($info['actors'] as $actor) {
                    $query = <<<'SQL'
                        INSERT INTO movie_cast (movie_id, person_id) VALUES (?, ?);
                    SQL;                        
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([$movieID, $actor]);
                }
            }
            
            $this->pdo->commit();                
            $this->disconnect();
            
            return ['id' => $movieID];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->disconnect();
            Utils::debug($e);
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return false;
        }
    }

    /**
     * Deletes a movie
     * 
     * @param   ID of the movie to delete
     * @return  an array with the film's ID,
     *      and empty array if the film was not found,
     *      or false if there was an error
     */
    public function delete(int $id): array|false
    {
        try {
            $this->pdo->beginTransaction();

            $query = <<<'SQL'
                DELETE FROM movie_director WHERE movie_id = ?;
            SQL;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            $affectedRows = $stmt->rowCount();

            $query = <<<'SQL'
                DELETE FROM movie_cast WHERE movie_id = ?;
            SQL;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            $affectedRows += $stmt->rowCount();

            $query = <<<'SQL'
                DELETE FROM movie WHERE movie_id = ?;
            SQL;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            $affectedRows += $stmt->rowCount();

            $this->pdo->commit();
            $this->disconnect();

            if ($affectedRows === 0) {
                return [];
            } else {
                return ['id' => $id];
            }

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->disconnect();
            Utils::debug($e);
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return false;
        }
    }
}