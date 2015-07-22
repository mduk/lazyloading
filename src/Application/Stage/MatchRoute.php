<?php

namespace Mduk\Application\Stage;

use Mduk\Application\Stage\Respond\NotFound as NotFoundResponseStage;
use Mduk\Application\Stage\Respond\MethodNotAllowed as MethodNotAllowedResponseStage;
use Mduk\Application\Stage\Builder as BuilderStage;

use Mduk\Service\Router\Exception as RouterException;

use Mduk\Gowi\Factory;
use Mduk\Gowi\Http\Application;
use Mduk\Gowi\Http\Application\Stage;
use Mduk\Gowi\Http\Request;
use Mduk\Gowi\Http\Response;

class MatchRoute implements Stage {

  public function execute( Application $app, Request $req, Response $res ) {
    try {
      $activeRoute = $app->getService( 'router' )
        ->request( 'route' )
        ->setParameter( 'path', $req->getPathInfo() )
        ->setParameter( 'method', $req->getMethod() )
        ->execute()
        ->getResults()
        ->shift();

      if ( !isset( $activeRoute['config']['builder'] ) ) {
        throw new \Exception( "Route config doesn't contain a builder name.\n" . print_r( $activeRoute['config'], true ) );
      }

      $builder = $activeRoute['config']['builder'];

      if ( !isset( $activeRoute['config']['config'] ) ) {
        throw new \Exception( "Route config doesn't contain any builder config" );
      }

      $builderConfig = $activeRoute['config']['config'];

      $newAppConfig = [
        'debug' => $app->getConfig( 'debug' ),
        'route' => [
          'pattern' => $activeRoute['route'],
          'parameters' => $activeRoute['params'],
        ]
      ];

      $builderFactory = new Factory( [
        'service-invocation' => function() {
          return new \Mduk\Application\Builder\ServiceInvocation;
        },
        'webtable' => function() {
          return new \Mduk\Application\Builder\WebTable;
        },
        'page' => function() {
          return new \Mduk\Application\Builder\Page;
        },
        'static-page' => function() {
          return new \Mduk\Application\Builder\StaticPage;
        }
      ] );

      return new BuilderStage(
        $builderFactory,
        $builder,
        $builderConfig,
        $newAppConfig
      );
    }
    catch ( RouterException\NotFound $e ) {
      return new NotFoundResponseStage;
    }
    catch ( RouterException\MethodNotAllowed $e ) {
      return new MethodNotAllowedResponseStage;
    }
  }

}