<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
class AuthenticationMiddleware {

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

    public function __invoke ( $request, $response, $next ) {

        /* -----------------------------------------
            Not logged in at all
        ------------------------------------------*/

        if ( !array_key_exists ( 'id', $_SESSION ) || trim ( $_SESSION['id'] ) == '' || !filter_var ( $_SESSION['id'], FILTER_VALIDATE_INT) ) {
            
            return $response->withStatus( 302 )->withHeader ( 'Location', '/signin/nolog' );
        }

        /* ----------------------------
            Not authenticated
            Incorrect authentication
        -----------------------------*/

        if ( !array_key_exists ( 'auth', $_SESSION ) || !is_string ( $_SESSION['auth'] ) || $_SESSION['auth'] != sha1 ( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return $response->withStatus ( 302 )->withHeader ( 'Location', '/signin/noauth' );
        }


       /* -----------------------------
            Time out functionality
        -----------------------------*/

        if ( !array_key_exists ( 'activity', $_SESSION ) || ( time() - $_SESSION['activity'] ) > 1800 ) {
            return $response->withStatus ( 302 )->withHeader ( 'Location', '/signin/noact' );
        }

        $_SESSION['activity'] = time(); //Set new time each action.

        //Session fixation
        if ( !array_key_exists ( 'fixation', $_SESSION ) || ( time() - $_SESSION['fixation'] > 1800 ) ) {
            session_regenerate_id ( true );
            $_SESSION['fixation'] = time();
        }


        //Everything passed with checks then continue with request.
        return $next ( $request, $response );
    }
}
