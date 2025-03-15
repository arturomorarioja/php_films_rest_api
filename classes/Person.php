<?php
/**
 * Person class
 * 
 * @author Arturo Mora-Rioja
 * @version 1.0.0 August 2020:
 * @version 1.0.1 December 2024. PSR standard enforced
 *                               Class design extended
 *                               Error handling improved
 */
require_once 'Connection.php';

class Person extends DB 
{
    /**
     * Retrieves the persons whose name includes a certain text
     * 
     * @param   text upon which to execute the search
     * @return array an array with person information, or false if there was an error
     */
    public function search(string $searchText): array|false
    {
        $query = <<<'SQL'
            SELECT person_id, person_name
            FROM person
            WHERE person_name LIKE ?
            ORDER BY person_name;
        SQL;

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['%' . $searchText . '%']);                
            
            $this->disconnect();
            
            return $stmt->fetchAll();                
        } catch (PDOException $e) {
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return false;
        }
    }

    /**
     * Inserts a new person
     * 
     * @param   name of the new person
     * @return array an array with the new person's ID, 
     *      -1 if the person already exists, 
     *      or 0 if there was an error
     */
    public function add(string $name): array|int
    {
        // Check if the person already exists
        $query = <<<'SQL'
            SELECT COUNT(*) AS total FROM person WHERE person_name = ?;
        SQL;
        try {

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$name]);            
            if ($stmt->fetch()['total'] > 0) {
                return -1;
            }
        } catch (PDOException $e) {
            Utils::debug($e);
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return 0;
        }

        // Insert the person
        $query = <<<'SQL'
            INSERT INTO person (person_name) VALUES (?);
        SQL;

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$name]);
            
            $newID = $this->pdo->lastInsertId();
            $this->disconnect();
            
            return ['id' => $newID];
        } catch (PDOException $e) {
            Utils::debug($e);
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return 0;
        }
    }

    /**
     * Deletes a person
     * 
     * @param   ID of the person to delete
     * @return array an array with the person's ID, 
     *      -1 if the person is associated to any movie,
     *      or 0 if there was an error
     */
    public function delete(int $id): array|int
    {

        // Check if the person is associated to any movie
        $query = <<<'SQL'
            SELECT COUNT(*) AS total FROM movie_director WHERE person_id = ?;
        SQL;
        try {

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            if ($stmt->fetch()['total'] > 0) {
                return -1;
            }
        } catch (PDOException $e) {
            Utils::debug($e);
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return 0;
        }

        $query = <<<'SQL'
            SELECT COUNT(*) AS total FROM movie_cast WHERE person_id = ?;
        SQL;
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            if ($stmt->fetch()['total'] > 0) {
                return -1;
            }
        } catch (PDOException $e) {
            Utils::debug($e);
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return 0;
        }

        // Delete the person
        $query = <<<'SQL'
            DELETE FROM person WHERE person_id = ?;
        SQL;

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            $this->disconnect();
            
            return ['id' => $id];
        } catch (PDOException $e) {
            Utils::debug($e);
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_QUERY . $e->getMessage();
            return 0;
        }
    }
}