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
     */
    public function registerDeviceAction(Request $request) {
        $data = $this->handleJSONRequest($request);
        if($data instanceof Response) {
            return $data;
        }

        if(!isset($data['deviceID']) || !isset($data['platform'])) {
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
        $data = json_decode($request->getContent(), true);
        if($data === null) {
            return new Response("Invalid Request JSON", 400);
        }
        return $data;
    }

    /**
     * @return Devices
     */
    private function getPushDevices() {
        return $this->get('push_devices');
    }
}
