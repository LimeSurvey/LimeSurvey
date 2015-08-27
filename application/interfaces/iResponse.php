<?php
namespace ls\interfaces;

interface iResponse {

    /**
     * @return string The UUID for this response.
     */
    public function getId();

    /**
     * @return string The token for this response, or null if none exists.
     */
    public function getToken();

    /**
     * Load the class given an ID,
     * @param string $id The UUID for this response.
     * @return self Returns the loaded response or null if not found.
     */
    public static function loadById($id);

}