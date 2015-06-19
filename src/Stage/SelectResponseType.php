<?php

namespace Mduk\Stage;

use Mduk\Gowi\Application;
use Mduk\Gowi\Application\Stage;
use Mduk\Gowi\Http\Request;
use Mduk\Gowi\Http\Response;

class SelectResponseType implements Stage {

  public function execute( Application $app, Request $req, Response $res ) {
    $supportedTypes = array_keys( $app->getConfig('active_route.config.response.transcoders') );
    $supportedTypes[] = '*/*';
    $acceptedTypes = $req->getAcceptableContentTypes();
    $selectedType = false;

    foreach ( $acceptedTypes as $aType ) {
      if ( in_array( $aType, $supportedTypes ) ) {
        $selectedType = $aType;
        break;
      }
    }

    if ( $selectedType == '*/*' ) {
      $selectedType = array_shift( $supportedTypes );
    }

    if ( !$selectedType ) {
      return new NotAcceptableResponseStage;
    }

    $app->setConfig( 'response.content_type', $selectedType );
  }

}
