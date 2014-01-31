<?php

namespace mcfedr\TwitterPushBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceController extends Controller {
    /**
     * @Route("/devices")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function registerDeviceAction(Request $request) {
        $data = $this->handleJSONRequest($request);
        if($data instanceof Response) {
            return $data;
        }

        if(!isset($data['deviceID']) || !isset($data['platform'])) {
            $this->get('logger')->error('Missing parameters', [
                'data' => $data
            ]);
            return new Response('Missing parameters', 400);
        }

        try {
            if(($arn = $this->getPushDevices()->registerDevice($data['deviceID'], $data['platform']))) {
                $this->get('logger')->info('Device registered', [
                    'arn' => $arn,
                    'device' => $data['deviceID'],
                    'platform' => $data['platform']
                ]);
                return new Response('Device registered', 200);
            }
        }
        catch(PlatformNotConfiguredException $e) {
            $this->get('logger')->error('Unknown platform', [
                'e' => $e,
                'platform' => $data['platform']
            ]);
            return new Response('Unknown platform', 400);
        }
        catch(\Exception $e) {
            $this->get('logger')->error('Exception registering device', [
                'e' => $e,
                'device' => $data['deviceID'],
                'platform' => $data['platform']
            ]);
        }

        return new Response('Unknown error', 500);
    }

    /**
     * Decode a json response
     *
     * @param Request $request
     * @return mixed|Response
     */
    private function handleJSONRequest(Request $request) {
        $content = $request->getContent();
        $data = json_decode($content, true);
        if($data === null) {
            $this->get('logger')->error('Invalid JSON', [
                'content' => $content
            ]);
            return new Response("Invalid Request JSON", 400);
        }
        return $data;
    }

    /**
     * @return Devices
     */
    private function getPushDevices() {
        return $this->get('mcfedr_aws_push.devices');
    }
}
