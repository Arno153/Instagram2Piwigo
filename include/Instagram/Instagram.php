<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

defined('INSTAGRAM_ROOT') or define('INSTAGRAM_ROOT', realpath(dirname(__FILE__)));

include_once(INSTAGRAM_ROOT.'/Core/Proxy.php');
include_once(INSTAGRAM_ROOT.'/Core/ApiException.php');
include_once(INSTAGRAM_ROOT.'/Net/ClientInterface.php');
include_once(INSTAGRAM_ROOT.'/Net/CurlClient.php');
include_once(INSTAGRAM_ROOT.'/Collection/MediaSearchCollection.php');
include_once(INSTAGRAM_ROOT.'/Collection/TagCollection.php');
include_once(INSTAGRAM_ROOT.'/Collection/UserCollection.php');
include_once(INSTAGRAM_ROOT.'/Collection/MediaCollection.php');
include_once(INSTAGRAM_ROOT.'/Collection/LocationCollection.php');
include_once(INSTAGRAM_ROOT.'/CurrentUser.php');
include_once(INSTAGRAM_ROOT.'/User.php');
include_once(INSTAGRAM_ROOT.'/Media.php');
include_once(INSTAGRAM_ROOT.'/Tag.php');
include_once(INSTAGRAM_ROOT.'/Location.php');


/**
 * Instagram!
 *
 * All objects are created through this object
 */
class Instagram extends Instagram_Core_BaseObjectAbstract {

    /**
     * Constructor
     *
     * You can supply a client, proxy, and an access token via the config array
     *
     * @param string $access_token Instagram access token obtained through authentication
     * @param Instagram_Net_ClientInterface $client Client object used to connect to the API
     * @access public
     */
    public function __construct( $access_token = null, Instagram_Net_ClientInterface $client = null ) {
        $this->proxy = new Instagram_Core_Proxy( $client ? $client : new Instagram_Net_CurlClient, $access_token ? $access_token : null );
    }
    
    /**
     * Enable cache system
     * @see Instagram_Core_Proxy->enableCache()
     */
    function enableCache ( $directory, $cache_expire = 600 ) {
        $this->proxy->enableCache( $directory, $cache_expire );
    }

    /**
     * Set the access token
     *
     * Most API calls require an access ID
     * 
     * @param string $access_token
     * @access public
     */
    public function setAccessToken( $access_token ) {
        $this->proxy->setAccessToken( $access_token );
    }

    /**
     * Set the client ID
     *
     * Some API calls can be called with only a Client ID
     * 
     * @param string $client_id Client ID
     * @access public
     */
    public function setClientID( $client_id ) {
        $this->proxy->setClientId( $client_id );
    }

    /**
     * Logout
     *
     * This doesn't actually work yet, waiting for Instagram to implement it in their API
     *
     * @access public
     */
    public function logout() {
        $this->proxy->logout();
    }

    /**
     * Get user
     *
     * Retrieve a user given his/her ID
     *
     * @param int $id ID of the user to retrieve
     * @return Instagram_User
     * @access public
     */
    public function getUser( $id ) {
        $user = new User( $this->proxy->getUser( $id ), $this->proxy );
        return $user;
    }

    /**
     * Get user by Username
     *
     * Retrieve a user given their username
     *
     * @param string $username Username of the user to retrieve
     * @return Instagram_User
     * @access public
     * @throws Instagram_ApiException
     */
    public function getUserByUsername( $username ) {
        $user = $this->searchUsers( $username, array( 'count' => 1 ) )->getItem( 0 );
        if ( $user ) {
            return $this->getUser( $user->getId() );
        }
        throw new Instagram_Core_ApiException( 'username not found', 400, 'InvalidUsername' );
    }

    /**
     * Check if a user is private
     *
     * @return bool
     * @access public
     */
    public function isUserPrivate( $user_id ) {
        $relationship = $this->proxy->getRelationshipToCurrentUser( $user_id );
        return (bool)$relationship->target_user_is_private;
    }

    /**
     * Get media
     *
     * Retreive a media object given it's ID
     *
     * @param int $id ID of the media to retrieve
     * @return Instagram_Media
     * @access public
     */
    public function getMedia( $id ) {
        $media = new Instagram_Media( $this->proxy->getMedia( $id ), $this->proxy );
        return $media;
    }

    /**
     * Get Tag
     *
     * @param string $tag Tag to retrieve
     * @return \Instagram\Tag
     * @access public
     */
    public function getTag( $tag ) {
        $tag = new Instagram_Tag( $this->proxy->getTag( $tag ), $this->proxy );
        return $tag;
    }

    /**
     * Get location
     *
     * Retreive a location given it's ID
     *
     * @param int $id ID of the location to retrieve
     * @return Instagram_Location
     * @access public
     */
    public function getLocation( $id ) {
        $location = new Instagram_Location( $this->proxy->getLocation( $id ), $this->proxy );
        return $location;
    }

    /**
     * Get current user
     *
     * Returns the current user wrapped in a Instagram_CurrentUser object
     *
     * @return Instagram_CurrentUser
     * @access public
     */
    public function getCurrentUser() {
        $current_user = new Instagram_CurrentUser( $this->proxy->getCurrentUser(), $this->proxy );
        return $current_user;
    }

    /**
     * Get popular media
     *
     * Returns current popular media
     *
     * @return Instagram_Collection_MediaCollection
     * @access public
     */
    public function getPopularMedia() {
        $popular_media = new Instagram_Collection_MediaCollection( $this->proxy->getPopularMedia(), $this->proxy );
        return $popular_media;
    }

    /**
     * Search users
     *
     * Search the users by username
     *
     * @param string $query Search query
     * @param array $params Optional params to pass to the endpoint
     * @return Instagram_Collection_UserCollection
     * @access public
     */
    public function searchUsers( $query, array $params = null ) {
        $params = (array)$params;
        $params['q'] = $query;
        $user_collection = new Instagram_Collection_UserCollection( $this->proxy->searchUsers( $params ), $this->proxy );
        return $user_collection;
    }

    /**
     * Search Media
     *
     * Returns media that is a certain distance from a given lat/lng
     *
     * To specify a distance, pass the distance (in meters) in the $params
     *
     * Default distance is 1000m
     *
     * @param float $lat Latitude of the search
     * @param float $lng Longitude of the search
     * @param array $params Optional params to pass to the endpoint
     * @return Instagram_Collection_MediaSearchCollection
     * @access public
     */
    public function searchMedia( $lat, $lng, array $params = null ) {
        $params = (array)$params;
        $params['lat'] = (float)$lat;
        $params['lng'] = (float)$lng;
        $media_collection =  new Instagram_Collection_MediaSearchCollection( $this->proxy->searchMedia( $params ), $this->proxy );
        return $media_collection;
    }

    /**
     * Search for tags
     *
     * @param string $query Search query
     * @param array $params Optional params to pass to the endpoint
     * @return Instagram_Collection_TagCollection
     * @access public
     */
    public function searchTags( $query, array $params = null ) {
        $params = (array)$params;
        $params['q'] = $query;
        $tag_collection =  new Instagram_Collection_TagCollection( $this->proxy->searchTags( $params ), $this->proxy );
        return $tag_collection;
    }

    /**
     * Search Locations
     *
     * Returns locations that are a certain distance from a given lat/lng
     *
     * To specify a distance, pass the distance (in meters) in the $params
     *
     * Default distance is 1000m
     *
     * @param float $lat Latitude of the search
     * @param float $lng Longitude of the search
     * @param array $params Optional params to pass to the endpoint
     * @return Instagram_LocationCollection
     * @access public
     */
    public function searchLocations( $lat, $lng, array $params = null ) {
        $params = (array)$params;
        $params['lat'] = (float)$lat;
        $params['lng'] = (float)$lng;
        $location_collection = new Instagram_Collection_LocationCollection( $this->proxy->searchLocations( $params ), $this->proxy );
        return $location_collection;
    }

}
?>