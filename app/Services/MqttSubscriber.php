<?php

namespace App\Services;

use PhpMqtt\Client\Facades\MQTT;
use App\Http\Controllers\MqttDataController;
use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Log;

class MqttSubscriber
{
    public function subscribe()
    {
        try {
            Log::info('Starting MQTT connection...');
            
            // Check if current time is 23:59
            $rooms = Room::all();
            $mqtt = MQTT::connection();
            foreach ($rooms as $room) {
                
                $mqtt->subscribe('COMPUTERLABROOM/SENSOR/' . $room->ten_phong, function ($topic, $message) {
                    Log::info('Received MQTT data: ' . $message);
                    
                    try {
                        // Tạo request giả lập
                        $request = Request::create(
                            '/api/mqtt-data',
                            'POST',
                            [],
                            [],
                            [],
                            ['CONTENT_TYPE' => 'application/json'],
                            $message
                        );
    
                        
                        // Gọi controller để xử lý dữ liệu
                        $controller = app()->make(MqttDataController::class);
                        $response = app()->call([$controller, 'handleMqttData'], ['request' => $request]);
    
                        Log::info('Processing result: ' . $response->getContent());
                    } catch (\Exception $e) {
                        Log::error('Error processing MQTT data: ' . $e->getMessage());
                    }
                }, 0);
            }
            $mqtt->loop(true);

            

        } catch (\Exception $e) {
            Log::error('MQTT connection error: ' . $e->getMessage());
            
            // Retry connection after 5 seconds
            sleep(5);
            $this->subscribe();
        }
    }
}