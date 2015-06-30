<?php

namespace Mduk\Stage;

use Mduk\Gowi\Http\Application;
use Mduk\Gowi\Http\Application\Stage;
use Mduk\Gowi\Http\Request;
use Mduk\Gowi\Http\Response;

class SelectRequestTranscoder implements Stage {

  public function execute( Application $app, Request $req, Response $res ) {
    if ( !$req->getContent() ) {
      return;
    }

    $requestContentType = $req->headers->get( 'Content-Type' );
    $requestTranscoderName = $app->getConfig( "http.request.transcoders.{$requestContentType}" );
    $requestTranscoder = $app->getConfig( "transcoder.{$requestTranscoderName}" );

    $app->setConfig( 'http.request.content_type', $requestContentType );
    $app->setConfig( 'http.request.transcoder', $requestTranscoder );
  }

}
