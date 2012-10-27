<?php
namespace Salvo\Bootstrap;

use Salvo\IBootstrap;
use Nucleus\Model\ProjectManagement\User;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

/**
 * This is a general purpose error handler for RESTAAPI calls returning error information in the content as JSON.
 */
class RestErrorHandler implements IBootstrap
{
    /**
     * This is method that the Salvo\Application executes when a bootstrap class is applied to it
     *
     * @param \Silex\Application $application
     */
    public function configure(Application $application)
    {
        $application->before(function($event) use ($application)
        {
            $requestUri = $event->getRequestUri();
            $restCommonString = $application['app_config']['rest_common_string'];

            //only register this error handler if the uri match the rest api string in it
            //todo: make this a confirguration option (the string)
            if(strpos($requestUri, $restCommonString))
            {
                $application->error(function (\Exception $exception, $code) use($application) {
                    $message = $exception->getMessage();

                    if(empty($message)) {
                        switch ($code) {
                            case 400:
                                $message = 'Bad Request';
                                break;
                            case 401:
                                $message = 'Unauthorized';
                                break;
                            case 404:
                                $message = 'Not Found';
                                break;
                            case 405:
                                $message = 'Method Not Allowed';
                                break;
                            case 415:
                                $message = 'Unsupported Media Type';
                                break;
                            default:
                                $message = 'Unknown Server Error';
                        }
                    }

                    $json = array(
                        'status' => 'error',
                        'error' => array(
                            'code' => $code,
                            'message' => $message
                        )
                    );

                    return $application->json($json);
                });
            }
        }, 1099);
    }
}